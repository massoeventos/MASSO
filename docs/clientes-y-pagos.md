# Clientes, pagos e inscripciones — cómo funciona

Este documento explica, en simple, dos cosas que se construyeron/reorganizaron en el Bloque 3 del plan de migración:

1. El módulo de **clientes** (cuentas de comprador, login sin password, identificación en el checkout).
2. Cómo quedó el **guardado de pagos e inscripciones** (`payments`, `payments_detail`, `events_enroll`) después de normalizar los datos duplicados.

No repite el detalle línea por línea del código — para eso está el código mismo. Esto es para entender la idea general sin tener que leer todos los archivos.

---

## 1. Módulo de clientes

### La idea central

Antes, cada vez que alguien compraba un ticket, tenía que volver a escribir todos sus datos (nombre, RUT, género, nacionalidad, ciudad...), aunque ya hubiera comprado antes. No existía ninguna noción de "este comprador ya existe".

Ahora existe una tabla `customers` que representa **al comprador**, separada de la tabla `payments` (que representa **una compra**). Un mismo `Customer` puede tener muchos `Payment` a lo largo del tiempo.

Punto importante: **el `Customer` nunca se crea a mano**. Nace solo, la primera vez que alguien compra con un correo que no existía antes. No hay pantalla de "Regístrate aquí".

### Cómo inicia sesión un cliente (sin contraseña obligatoria)

La forma normal de identificarse es con un **código de 6 dígitos** que se manda por correo (y, si el cliente tiene teléfono guardado, también puede pedirse por SMS vía Twilio como alternativa). Nunca es obligatorio tener contraseña.

Si el cliente quiere, puede configurar una contraseña **desde su cuenta** (`/mi-cuenta/perfil`) para entrar más rápido la próxima vez sin esperar el código. Pero nunca se le pide ni se le ofrece durante una compra — eso sería fricción justo en el momento en que no queremos perder al comprador.

Este mecanismo de código (generarlo, mandarlo, verificarlo, limitar reintentos) es **uno solo** y se reutiliza tanto para el login normal como para identificarse durante el checkout. Vive en `app/Services/Otp/LoginCodeService.php`, con dos "canales" de envío intercambiables: `MailOtpChannel` (correo) y `TwilioSmsChannel` (SMS).

### El paso de identificación en el checkout

Al entrar a comprar un ticket, aparece un modal pidiendo el correo, **antes** de poder tocar el resto del formulario (evita que alguien llene todo y recién al final se dé cuenta de que hay que identificarse). Según lo que responda el sistema:

- **Correo nuevo** (nunca compró antes): el modal se cierra solo. Cero fricción — llena el formulario normal, como cualquier compra de siempre.
- **Correo con datos guardados**: el modal pide el código de 6 dígitos. Al verificarlo, el formulario se autocompleta con los datos que ya tenía guardados, y esos campos quedan bloqueados (de solo lectura) — si necesita corregir algo, hay un link a "Mi Perfil" para hacerlo ahí, no en el checkout.
- **Ya tiene sesión activa** (por ejemplo, entró antes a `/mi-cuenta`): ni siquiera aparece el modal — se autocompleta directo, con un aviso de que fue por sesión activa.

Los campos que el cliente **todavía no tenía guardados** (por ejemplo, nunca puso su género) quedan editables normalmente, y esa primera vez que los completa, quedan guardados en su perfil para la próxima compra.

### Qué son los datos "default" del cliente

Se decidió que estos campos viven **una sola vez en el `Customer`**, y nunca se repiten en cada compra: nombre, apellido, email, RUT o pasaporte, género, nacionalidad, y ciudad/país de residencia.

Los datos de **facturación** (boleta o factura, razón social, etc.) quedaron **fuera** de esto a propósito — se siguen pidiendo en cada compra, porque pueden variar (una vez boleta personal, otra vez factura de una empresa distinta).

### La cuenta del cliente (`/mi-cuenta`)

Área separada del panel de administración del staff (usa su propio sistema de login, un "guard" distinto). Desde ahí el cliente puede:

- Ver su historial de compras ("Mis Compras").
- Editar sus datos personales, configurar/cambiar su contraseña, y agregar un teléfono (que se confirma por SMS antes de guardarse, para que nadie pueda dejar el número de otra persona como respaldo).

**Importante:** el historial de compras de un cliente **solo muestra compras hechas después de que se implementó este módulo**. No se hizo ningún proceso para "adivinar" a qué cliente pertenecían las ~43.000 compras históricas — esos correos antiguos tienen errores y duplicados conocidos, y mezclarlos con el sistema nuevo de identidad iba a traer más problemas que soluciones. Un comprador antiguo simplemente empieza su historial desde su primera compra nueva.

### Archivos principales

| Qué | Dónde |
|---|---|
| El comprador | `app/Customer.php` |
| Códigos de acceso (OTP) | `app/CustomerLoginCode.php` |
| Generar/enviar/verificar códigos | `app/Services/Otp/LoginCodeService.php`, `MailOtpChannel.php`, `TwilioSmsChannel.php` |
| Cuenta del cliente (login, perfil, historial) | `app/Http/Controllers/Customer/AccountController.php` |
| Identificación durante el checkout | `app/Http/Controllers/Guest/PublicController.php` (métodos `identifyCustomer`, `verifyCustomerCode`, `linkCustomerToPurchase`) |
| Vistas de cliente | `resources/views/customer/*` |
| Modal de identificación en el checkout | `resources/views/guest/register.blade.php` |

---

## 2. Cómo se guardan los pagos y las inscripciones

### El problema original

El sistema guardaba el mismo dato en varios lugares a la vez:

- Una columna real en la tabla (ej. `payments.name`), **y además**
- El mismo dato metido dentro de un bloque de texto serializado (`payments.data`), **y a veces también**
- Copiado otra vez en `events_enroll` cuando el pago se confirmaba.

Si alguien compraba dos tickets en un mismo pago, sus datos quedaban triplicados. Y como no había una sola fuente de verdad, era fácil que un dato quedara desactualizado en un lugar y no en otro.

### Cómo quedó ahora (de la compra al asistente)

```
Formulario de compra
        │
        ▼
   Customer  (el comprador — nombre, RUT, género, etc. UNA sola vez)
        │
        ▼
   Payment   (una transacción — monto, estado, evento, facturación...
              los datos personales del comprador NO se repiten aquí:
              se leen desde el Customer vinculado)
        │
        ▼
PaymentDetail (una fila por cada ticket comprado dentro de ese pago,
               con su propio estado: reservado / confirmado / anulado / reemplazado)
        │
        ▼
 EventEnroll  (quién asiste al evento — para asistentes que vienen de
               un pago, tampoco repite los datos: solo enlaza al ticket
               pagado y los resuelve desde ahí)
```

**La idea clave que se repite en cada nivel:** en vez de copiar el dato, se guarda un enlace al lugar donde el dato realmente vive, y se "resuelve" ese dato a través del enlace cuando se necesita mostrar. Esto se hace con *accessors* de Eloquent — un método en el modelo que intercepta cuando alguien pide, por ejemplo, `$payment->name`, y en vez de leer la columna directamente, primero revisa si hay un `Customer` vinculado: si lo hay y tiene el dato, devuelve ese; si no, recién ahí devuelve la columna propia del pago.

Esto es importante porque significa que **el resto del código no tuvo que cambiar** — el panel de administración, los correos de confirmación, todo sigue escribiendo `$payment->name` normalmente, sin saber ni importarle si el dato viene de la columna propia o del cliente vinculado.

### Por qué las compras viejas no se rompieron

Todo esto se hizo de forma que **solo aplica a compras nuevas**. Una compra vieja, hecha antes de este cambio, no tiene ningún `Customer` vinculado (`customer_id` es `null`), así que el accessor simplemente devuelve la columna propia del pago, exactamente como funcionaba antes. Nada del historial cambió de comportamiento.

Este mismo principio ("enlazar en vez de copiar, con reglas para no romper lo viejo") ya se había aplicado antes entre `payments_detail` y `events_enroll` — un asistente que viene de un pago ya no duplica sus datos en `events_enroll`, sino que la fila solo guarda a qué ticket pagado corresponde (`payment_detail_id`), y el modelo resuelve el dato desde ahí. Los invitados/cortesías creados a mano en el panel (sin ningún pago de por medio) siguen siendo un caso aparte, con sus propias columnas llenas directamente, sin enlace a nada.

### Qué pasa con la facturación (boleta/factura)

A diferencia del resto de los datos personales, **la facturación se guarda tal cual en cada `Payment`**, sin resolver nada a través del `Customer`. Fue una decisión explícita: una misma persona puede pedir boleta personal una vez y factura de una empresa distinta otra vez, así que no tiene sentido que sea un dato "de una vez" en el perfil.

### El email es un caso especial

El email **sí** se resuelve a través del `Customer`, igual que el resto de los datos personales — con una excepción: si el staff necesita corregir manualmente el email de un pago pendiente desde el panel de administración (por un typo, por ejemplo), esa edición ahora se guarda en el `Customer`, no en la columna del pago (que ya no se lee). Si el nuevo correo ya le pertenece a otro cliente, el sistema rechaza el cambio — nunca se mezclan dos identidades automáticamente.

### `payments.data` / `payments.data_json` — ya no se generan

Estas dos columnas guardaban un "respaldo" de todo el formulario: `data` como un blob de texto serializado con todo, y `data_json` como una versión en JSON con solo los campos que no tenían columna real. Se revisó dónde se **leían** hoy y no se encontró ningún lugar en el código actual que las use — ni en el panel admin, ni en los correos, ni en ningún export. El método que las desserializaba (`Payment::processData()`) no lo llama nada. Los campos personalizados del evento (que antes eran la razón principal para guardar el JSON) ya se guardan aparte, normalizados, en `event_input_values`.

Por eso, desde ahora, `process()` y `processPay()` (en `PublicController`) **ya no las escriben** en pagos nuevos. Las columnas siguen existiendo en la tabla y los pagos viejos que ya las tenían no se tocan — no se borra nada histórico sin validar antes en un ambiente de pruebas, esa regla se mantiene. Esto es distinto de `events_enroll.data`/`events_enroll.data_json`, que es otra tabla y no se tocó.

### Lo que nunca se tocó ni se borró

- Ninguna columna ni tabla legada se elimina — solo se dejó de escribir en `payments.data`/`data_json` para pagos nuevos, como se explicó arriba.
- `events_enroll` sigue siendo la tabla central de "quién va a asistir" — el panel de administración sigue leyendo de ahí exactamente igual que antes, solo cambió de dónde el modelo saca el dato internamente.
