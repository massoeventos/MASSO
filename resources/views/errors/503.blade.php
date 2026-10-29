{{-- Página de mantenimiento (php artisan down --render="errors::503").
     Autocontenida: sin mix() ni Vite, porque durante el deploy los assets se
     están recompilando. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masso Eventos - En mantenimiento</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; background: #f5f5f5; color: #333; }
        .box { max-width: 480px; margin: 16px; padding: 40px 32px; background: #fff; border-radius: 8px;
               box-shadow: 0 2px 12px rgba(0,0,0,.08); text-align: center; }
        img { max-width: 160px; margin-bottom: 24px; }
        h1 { font-size: 22px; margin: 0 0 12px; }
        p { margin: 0; line-height: 1.5; color: #666; }
    </style>
</head>
<body>
    <div class="box">
        <img src="/images/logo.jpg" alt="Masso Eventos">
        <h1>Estamos actualizando el sitio</h1>
        <p>Volvemos en unos minutos. Gracias por la paciencia.</p>
    </div>
</body>
</html>
