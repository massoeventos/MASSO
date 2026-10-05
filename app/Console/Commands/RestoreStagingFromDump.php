<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Reproduce, de punta a punta y de forma repetible, el proceso de levantar
 * un entorno de staging (Laravel Cloud) a partir de una copia (dump SQL)
 * de PROD:
 *
 *   1. Importa el dump en la conexión de BD configurada por .env.
 *   2. Corre las migraciones pendientes (data_json, event_input_values,
 *      payments_detail.status, events_enroll.payment_detail_id,
 *      payments.passport/participants_*, ver ítems 1.3/1.5/2.1/2.2 del
 *      plan de migración).
 *   3. Corre todos los backfills en el orden correcto (ver handle()) —
 *      el orden importa: backfill-participants-columns y
 *      backfill-payment-passport leen del blob legado `data` (no de
 *      data_json) precisamente para no depender de en qué momento corra
 *      masso:backfill-data-json.
 *
 * No inventa infraestructura nueva: orquesta el mismo `mysql` cliente y
 * los mismos comandos artisan que ya existen, para que el proceso sea
 * reproducible sesión a sesión en vez de una receta manual.
 */
class RestoreStagingFromDump extends Command
{
    protected $signature = 'masso:restore-staging-from-dump
        {dump? : Ruta al archivo .sql exportado desde PROD}
        {--skip-import : No importar el dump, solo correr migraciones + backfills}
        {--force : Confirma que se acepta reemplazar los datos de la BD configurada}';

    protected $description = 'Levanta un entorno de staging desde una copia de PROD: importa el dump, migra y corre todos los backfills de datos (1.3/1.5/2.1/2.2)';

    public function handle()
    {
        // Salvaguarda: este comando reemplaza datos y está pensado
        // exclusivamente para staging/desarrollo, nunca para producción.
        if (app()->environment('production')) {
            $this->error('Este comando no puede correr con APP_ENV=production. Configura un entorno de staging.');
            return 1;
        }

        if (!$this->option('skip-import')) {
            $dumpPath = $this->argument('dump');

            if (empty($dumpPath)) {
                $this->error('Debes indicar la ruta al dump: masso:restore-staging-from-dump /ruta/al/dump.sql --force');
                return 1;
            }

            if (!file_exists($dumpPath)) {
                $this->error("No existe el archivo: {$dumpPath}");
                return 1;
            }

            if (!$this->option('force')) {
                $database = config('database.connections.' . config('database.default') . '.database');
                $this->error("Este comando reemplaza los datos de la BD configurada en .env ({$database}). Vuelve a ejecutar agregando --force para confirmar.");
                return 1;
            }

            $this->info("Importando {$dumpPath} en la base de datos configurada...");
            $exitCode = $this->importDump($dumpPath);

            if ($exitCode !== 0) {
                $this->error('Falló la importación del dump. Revisa el mensaje de mysql arriba.');
                return $exitCode;
            }

            $this->info('Dump importado correctamente.');
        }

        $this->info('Corriendo migraciones pendientes...');
        $this->call('migrate', ['--force' => true]);

        // Orden: primero las columnas promovidas desde el blob que leen
        // `data` crudo (independientes de data_json), después data_json
        // en sí, después lo que depende de data_json, y por último el
        // ciclo de vida de payments_detail/events_enroll (independiente
        // de todo lo anterior).
        $this->info('Backfill de payments.participants_excel_file/participants_count...');
        $this->call('masso:backfill-participants-columns');

        $this->info('Backfill de payments.passport...');
        $this->call('masso:backfill-payment-passport');

        $this->info('Backfill de data_json (payments)...');
        $this->call('masso:backfill-data-json', ['table' => 'payments']);

        $this->info('Backfill de data_json (events_enroll)...');
        $this->call('masso:backfill-data-json', ['table' => 'events_enroll']);

        $this->info('Backfill de event_input_values...');
        $this->call('masso:backfill-event-input-values');

        $this->info('Backfill de payments_detail.status...');
        $this->call('masso:backfill-payment-detail-status');

        $this->info('Backfill de events_enroll.payment_detail_id...');
        $this->call('masso:backfill-event-enroll-payment-detail');

        $this->newLine();
        $this->info('Listo. Para verificar antes de confiar en el entorno, corré:');
        $this->line('  php artisan masso:audit-serialized-data all');
        $this->line('  php artisan masso:audit-duplicate-fields payments');
        $this->line('  php artisan masso:reconcile-enrollment-export');

        return 0;
    }

    private function importDump(string $dumpPath): int
    {
        $finder = new ExecutableFinder();
        $mysqlBinary = $finder->find('mysql');

        if ($mysqlBinary === null) {
            $this->error('No se encontró el cliente `mysql` en el PATH de este entorno. Importa el dump manualmente y vuelve a correr este comando con --skip-import.');
            return 1;
        }

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $process = new Process([
            $mysqlBinary,
            '--host=' . $config['host'],
            '--port=' . ($config['port'] ?? 3306),
            '--user=' . $config['username'],
            $config['database'],
        ]);

        // La password va por variable de entorno (MYSQL_PWD) y no como
        // argumento de línea de comandos, para que no quede visible en la
        // lista de procesos del sistema (ps/tasklist).
        $process->setEnv(['MYSQL_PWD' => $config['password']]);
        $process->setInput(fopen($dumpPath, 'r'));
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode();
    }
}
