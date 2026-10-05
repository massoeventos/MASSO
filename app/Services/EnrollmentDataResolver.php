<?php

namespace Masso\Services;

/**
 * Única fuente de verdad para decidir qué campos de payments/events_enroll
 * ya tienen columna real dedicada y por lo tanto NUNCA deben repetirse
 * como "campo extra" al leer el blob legado (payments.data /
 * events_enroll.data, o su versión limpia data_json).
 *
 * Reemplaza tres listas que existían por separado y podían desincronizarse
 * entre sí: EventEnroll::$field_private, EnrollController::$field_private
 * y la lista de exclusión inline que tenía EnrollController::index().
 *
 * Regla de precedencia (pedida para el export de inscritos, a nivel de
 * evento): la columna real de la tabla SIEMPRE gana sobre el valor
 * equivalente que pueda venir dentro del blob serializado/JSON. Esta
 * clase no intenta "elegir el mejor valor" entre ambos: descarta del
 * blob cualquier clave que ya tenga columna, de modo que el único valor
 * que sobrevive para esas claves es el de la columna real.
 */
class EnrollmentDataResolver
{
    public const KNOWN_KEYS = [
        // Metadatos del formulario, nunca son un campo de negocio real.
        '_token',
        // Columnas reales compartidas por payments y/o events_enroll.
        'name', 'lastname', 'rut', 'passport', 'email', 'phone',
        'profession', 'speciality', 'workplace',
        'city', 'city_id', 'country', 'country_id', 'custom_city', 'region_id',
        'nationality_country_id',
        'gender',
        'ticket', 'ticket_id', 'event_id',
        'payment', 'amount', 'ids', 'available', 'check',
        'status', 'type', 'managment', 'has_inscription',
        'billing_method', 'invoice_data',
        'description',
        'coupon_code', 'coupon_id', 'discount_percentage', 'discount_amount',
        'user_observation', 'purchase_order_type', 'purchase_order_number', 'purchase_order_file',
        'participants_excel_file', 'participants_count',
    ];

    /**
     * Filtra el array ya deserializado del blob legado (data/data_json),
     * dejando solo las claves que NO tienen columna dedicada: por ejemplo,
     * las respuestas a los campos dinámicos de events_inputs.
     */
    public static function extraFields(array $rawData): array
    {
        return array_diff_key($rawData, array_flip(self::KNOWN_KEYS));
    }

    /**
     * Combina los campos ya resueltos desde columnas reales con los campos
     * extra del blob. $columnFields se aplica al final: una columna real
     * nunca puede quedar pisada por el blob, incluso si alguna clave se
     * hubiera olvidado agregar a KNOWN_KEYS.
     */
    public static function mergeWithColumns(array $columnFields, array $rawData): array
    {
        return array_merge(self::extraFields($rawData), $columnFields);
    }

    /**
     * Para columnas que en el flujo actual suelen quedar vacías (por
     * ejemplo events_enroll.phone/profession/speciality/workplace, que ya
     * no se llenan desde el formulario público): devuelve la columna real
     * si tiene valor, y solo si está vacía cae al mismo campo dentro del
     * blob legado. La columna real sigue ganando siempre que exista.
     */
    public static function columnOrFallback($columnValue, array $rawData, string $key)
    {
        if ($columnValue !== null && $columnValue !== '') {
            return $columnValue;
        }

        return $rawData[$key] ?? $columnValue;
    }
}
