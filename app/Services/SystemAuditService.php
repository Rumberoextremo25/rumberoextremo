<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Auditoría técnica del sistema para el backoffice.
 *
 * No modifica nada por sí sola: recopila señales, calcula un estado y ofrece
 * acciones seguras de mantenimiento. Las acciones destructivas (migraciones,
 * borrado de datos o cambios de .env) solo se recomiendan como comando.
 */
class SystemAuditService
{
    private ?Carbon $previousRunAt = null;

    public const FIX_ACTIONS = [
        'optimize_clear' => [
            'label' => 'Limpiar todas las cachés',
            'command' => 'optimize:clear',
            'confirm' => '¿Limpiar configuración, rutas, vistas y caché de aplicación?',
        ],
        'cache_clear' => [
            'label' => 'Limpiar caché de aplicación',
            'command' => 'cache:clear',
            'confirm' => '¿Vaciar la caché de aplicación?',
        ],
        'view_clear' => [
            'label' => 'Recompilar vistas',
            'command' => 'view:clear',
            'confirm' => '¿Eliminar las vistas compiladas para que se regeneren?',
        ],
        'config_clear' => [
            'label' => 'Limpiar configuración cacheada',
            'command' => 'config:clear',
            'confirm' => '¿Eliminar la configuración cacheada?',
        ],
        'route_clear' => [
            'label' => 'Limpiar rutas cacheadas',
            'command' => 'route:clear',
            'confirm' => '¿Eliminar las rutas cacheadas?',
        ],
        'storage_link' => [
            'label' => 'Crear enlace de storage',
            'command' => 'storage:link',
            'confirm' => '¿Crear el enlace public/storage hacia storage/app/public?',
        ],
        'queue_restart' => [
            'label' => 'Reiniciar workers',
            'command' => 'queue:restart',
            'confirm' => '¿Enviar la señal de reinicio a los workers de cola?',
        ],
        'test_ai' => [
            'label' => 'Probar proveedores de IA',
            'command' => null,
            'confirm' => 'Se hará una llamada real a Gemini y DeepSeek. ¿Continuar?',
        ],
    ];

    public function run(): array
    {
        $previous = $this->last();
        $this->previousRunAt = null;

        if ($previous && ! empty($previous['generated_at'])) {
            try {
                $this->previousRunAt = Carbon::parse($previous['generated_at']);
            } catch (Throwable) {
                // Si la fecha guardada no es válida, se usa la ventana de 24 h.
            }
        }

        $checks = [];
        $methods = [
            'checkEnvironment',
            'checkDatabase',
            'checkStorage',
            'checkCacheQueueSessions',
            'checkSecurity',
            'checkIntegrations',
            'checkLogs',
            'checkPerformance',
        ];

        foreach ($methods as $method) {
            try {
                $this->{$method}($checks);
            } catch (Throwable $e) {
                $this->add(
                    $checks,
                    'audit.' . Str::snake($method),
                    'Sistema',
                    'No se pudo completar una comprobación',
                    'critical',
                    class_basename($e),
                    'La comprobación falló: ' . Str::limit($e->getMessage(), 240),
                    'Revisa storage/logs/laravel.log y vuelve a ejecutar la auditoría.'
                );
            }
        }

        $summary = $this->summarize($checks);
        $result = [
            'generated_at' => now()->toIso8601String(),
            'generated_by' => auth()->user()?->email,
            'environment' => [
                'app_env' => (string) config('app.env'),
                'app_url' => (string) config('app.url'),
                'laravel' => app()->version(),
                'php' => PHP_VERSION,
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? PHP_SAPI,
                'database' => (string) config('database.default'),
                'cache' => (string) config('cache.default'),
                'queue' => (string) config('queue.default'),
                'session' => (string) config('session.driver'),
                'timezone' => (string) config('app.timezone'),
            ],
            'summary' => $summary,
            'checks' => $checks,
            'recommendations' => $this->buildRecommendations($checks),
        ];

        $this->store($result);

        return $result;
    }

    public function last(): ?array
    {
        $path = $this->storagePath('last.json');

        if (! File::isFile($path)) {
            return null;
        }

        try {
            $data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

            return is_array($data) && isset($data['checks'], $data['summary']) ? $data : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function history(): array
    {
        $path = $this->storagePath('history.json');

        if (! File::isFile($path)) {
            return [];
        }

        try {
            $data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

            return is_array($data) ? $data : [];
        } catch (Throwable) {
            return [];
        }
    }

    public function allowedEmails(): array
    {
        // La configuración guardada desde el backoffice tiene prioridad sobre
        // el .env; así el propietario puede restringir el acceso sin editar
        // archivos ni esperar a un despliegue.
        try {
            $stored = \App\Models\Setting::query()
                ->where('key', 'system_audit_emails')
                ->value('value');
        } catch (Throwable) {
            $stored = null;
        }

        // Si existe la fila (aunque esté vacía), manda sobre el .env. Así el
        // propietario puede quitar la restricción desde el backoffice aunque
        // SYSTEM_AUDIT_EMAILS siga definido en el servidor.
        $emails = $stored !== null
            ? explode(',', (string) $stored)
            : config('system_audit.allowed_emails', []);

        if (is_string($emails)) {
            $emails = explode(',', $emails);
        }

        if (! is_array($emails)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($email) => strtolower(trim((string) $email)),
            $emails
        )));
    }

    public function fix(string $action): array
    {
        if (! array_key_exists($action, self::FIX_ACTIONS)) {
            throw new \InvalidArgumentException('Acción de auditoría no permitida.');
        }

        if ($action === 'test_ai') {
            $result = $this->testAi();
        } else {
            $definition = self::FIX_ACTIONS[$action];
            $command = (string) $definition['command'];

            if ($action === 'storage_link' && (is_link(public_path('storage')) || is_dir(public_path('storage')))) {
                $result = [
                    'ok' => true,
                    'message' => 'El enlace public/storage ya existe; no se modificó nada.',
                    'output' => '',
                ];
            } else {
                try {
                    $exitCode = Artisan::call($command);
                    $output = trim(Artisan::output());
                    $result = [
                        'ok' => $exitCode === 0,
                        'message' => $exitCode === 0
                            ? 'Acción completada: ' . $definition['label'] . '.'
                            : 'El comando terminó con código ' . $exitCode . '. Revisa la salida.',
                        'output' => $output,
                    ];
                } catch (Throwable $e) {
                    $result = [
                        'ok' => false,
                        'message' => 'No se pudo completar la acción: ' . Str::limit($e->getMessage(), 240),
                        'output' => '',
                    ];
                }
            }
        }

        Log::info('system_audit.fix', [
            'action' => $action,
            'ok' => $result['ok'],
            'user_id' => auth()->id(),
        ]);

        return $result;
    }

    private function checkEnvironment(array &$checks): void
    {
        $isProduction = app()->environment('production');
        $phpOk = version_compare(PHP_VERSION, '8.2.0', '>=');
        $this->add(
            $checks,
            'php.version',
            'Entorno',
            'Versión de PHP',
            $phpOk ? 'ok' : 'critical',
            PHP_VERSION,
            $phpOk
                ? 'PHP ' . PHP_VERSION . ' con soporte para Laravel.'
                : 'PHP ' . PHP_VERSION . ' está por debajo de 8.2; Laravel puede fallar.',
            $phpOk ? null : 'Actualiza PHP a 8.2 o superior desde Forge.',
            null,
            $phpOk ? null : 'sudo systemctl status php8.2-fpm'
        );

        $required = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'curl', 'fileinfo'];
        $driver = (string) config('database.default');
        $driverExtension = [
            'mysql' => 'pdo_mysql',
            'mariadb' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
            'sqlsrv' => 'pdo_sqlsrv',
        ][$driver] ?? null;

        if ($driverExtension) {
            $required[] = $driverExtension;
        }

        $missing = array_values(array_filter($required, static fn ($extension) => ! extension_loaded($extension)));
        $this->add(
            $checks,
            'php.extensions',
            'Entorno',
            'Extensiones de PHP',
            $missing === [] ? 'ok' : 'critical',
            $missing === [] ? count($required) . ' requeridas cargadas' : 'Faltan: ' . implode(', ', $missing),
            $missing === []
                ? 'Todas las extensiones necesarias para Laravel y la base de datos están activas.'
                : 'Faltan extensiones que Laravel o la base de datos necesitan para funcionar.',
            $missing === [] ? null : 'Instala/activa las extensiones desde Forge o el servidor.',
            null,
            $missing === [] ? null : 'php -m'
        );

        $debug = (bool) config('app.debug');
        $debugStatus = $isProduction && $debug ? 'critical' : ($debug ? 'warning' : 'ok');
        $this->add(
            $checks,
            'app.debug',
            'Entorno',
            'Modo depuración',
            $debugStatus,
            $debug ? 'APP_DEBUG=true' : 'APP_DEBUG=false',
            $isProduction && $debug
                ? 'En producción, APP_DEBUG=true expone rutas, consultas, variables y datos internos.'
                : ($debug ? 'Estás en un entorno que no es producción; recuerda desactivarlo al desplegar.' : 'La depuración está desactivada.'),
            $debug ? 'Define APP_DEBUG=false en el .env de producción.' : null,
            null,
            $debug ? 'php artisan config:clear' : null
        );

        $key = (string) config('app.key');
        $this->add(
            $checks,
            'app.key',
            'Entorno',
            'APP_KEY',
            $key !== '' ? 'ok' : 'critical',
            $key !== '' ? 'Configurada' : 'Vacía',
            $key !== ''
                ? 'La clave de cifrado está configurada; sesiones y datos cifrados pueden abrirse.'
                : 'Sin APP_KEY Laravel no puede cifrar sesiones, cookies ni datos sensibles.',
            $key !== '' ? null : 'Genera una clave con: php artisan key:generate --show y colócala en .env.',
            null,
            $key !== '' ? null : 'php artisan key:generate --show'
        );

        $url = (string) config('app.url');
        $https = str_starts_with(strtolower($url), 'https://');
        $this->add(
            $checks,
            'app.url',
            'Entorno',
            'URL y HTTPS',
            $https ? 'ok' : ($isProduction ? 'critical' : 'warning'),
            $url !== '' ? $url : 'Sin APP_URL',
            $https
                ? 'La URL de la aplicación usa HTTPS.'
                : 'APP_URL no usa HTTPS; las cookies seguras y los enlaces pueden fallar.',
            $https ? null : 'Configura APP_URL con https:// y fuerza HTTPS en el servidor.',
            null,
            $https ? null : 'php artisan config:clear'
        );

        $memory = $this->iniBytes((string) ini_get('memory_limit'));
        $memoryOk = $memory < 0 || $memory >= 256 * 1024 * 1024;
        $this->add(
            $checks,
            'php.memory',
            'Entorno',
            'Memoria de PHP',
            $memoryOk ? 'ok' : 'warning',
            $memory < 0 ? 'Sin límite' : $this->formatBytes($memory),
            $memoryOk
                ? 'El límite de memoria es suficiente para el panel y los procesos normales.'
                : 'Un límite menor a 256 MB puede cortar importaciones, reportes o generación de PDFs.',
            $memoryOk ? null : 'Sube memory_limit a 256M o más.',
            null,
            null
        );

        $maxExecution = (int) ini_get('max_execution_time');
        $executionOk = $maxExecution === 0 || $maxExecution >= 30;
        $this->add(
            $checks,
            'php.max_execution',
            'Entorno',
            'Tiempo máximo de ejecución',
            $executionOk ? 'ok' : 'warning',
            $maxExecution === 0 ? 'Sin límite' : $maxExecution . ' s',
            $executionOk
                ? 'El tiempo máximo permite operaciones normales.'
                : 'Menos de 30 segundos puede cortar exportaciones o procesos pesados.',
            $executionOk ? null : 'Sube max_execution_time a 60-120 s.',
            null,
            null
        );

        $this->add(
            $checks,
            'app.env',
            'Entorno',
            'Entorno de ejecución',
            $isProduction ? 'ok' : 'warning',
            app()->environment(),
            $isProduction
                ? 'La aplicación está en producción.'
                : 'La aplicación no está en producción; no uses este entorno para validar seguridad.',
            $isProduction ? null : 'Verifica APP_ENV=production en el despliegue.',
            null,
            null
        );

        $this->add(
            $checks,
            'app.timezone',
            'Entorno',
            'Zona horaria',
            in_array((string) config('app.timezone'), ['UTC', 'America/Caracas'], true) ? 'ok' : 'info',
            (string) config('app.timezone'),
            'Zona horaria usada para fechas, reportes y vencimientos.',
            null,
            null,
            null
        );

        $this->add(
            $checks,
            'app.maintenance',
            'Entorno',
            'Modo mantenimiento',
            app()->isDownForMaintenance() ? 'warning' : 'ok',
            app()->isDownForMaintenance() ? 'Activo' : 'Inactivo',
            app()->isDownForMaintenance()
                ? 'La aplicación está en mantenimiento y los usuarios no pueden entrar.'
                : 'La aplicación está disponible para los usuarios.',
            app()->isDownForMaintenance() ? 'Desactiva el mantenimiento cuando termines.' : null,
            null,
            app()->isDownForMaintenance() ? 'php artisan up' : null
        );
    }

    private function checkDatabase(array &$checks): void
    {
        $start = microtime(true);
        $connected = true;
        $error = null;

        try {
            DB::connection()->getPdo();
            DB::select('select 1');
        } catch (Throwable $e) {
            $connected = false;
            $error = $e->getMessage();
        }

        $latency = (int) round((microtime(true) - $start) * 1000);
        $slow = $connected && $latency > 500;
        $this->add(
            $checks,
            'db.connection',
            'Base de datos',
            'Conexión',
            $connected ? ($slow ? 'warning' : 'ok') : 'critical',
            $connected ? $latency . ' ms' : 'Sin conexión',
            $connected
                ? ($slow
                    ? 'La base de datos respondió, pero con latencia alta (>500 ms).'
                    : 'La base de datos respondió correctamente.')
                : 'No se pudo conectar: ' . Str::limit((string) $error, 220),
            $connected
                ? ($slow ? 'Revisa la latencia de red, conexiones abiertas y carga de la base de datos.' : null)
                : 'Revisa DB_* en .env, el firewall y el estado de la base de datos.',
            null,
            $connected ? null : 'php artisan db:show'
        );

        if (! $connected) {
            return;
        }

        try {
            $driver = DB::connection()->getDriverName();
            $database = DB::connection()->getDatabaseName();
            $this->add(
                $checks,
                'db.info',
                'Base de datos',
                'Motor y base',
                'info',
                $driver . ' / ' . $database,
                'Conexión activa con el motor de base de datos configurado.',
                null,
                null,
                null
            );
        } catch (Throwable) {
            // La conexión ya se comprobó; este dato es solo informativo.
        }

        try {
            if (! Schema::hasTable('migrations')) {
                $this->add(
                    $checks,
                    'db.migrations_table',
                    'Base de datos',
                    'Tabla de migraciones',
                    'critical',
                    'No existe',
                    'No hay tabla migrations: el esquema no está controlado por Laravel.',
                    'Ejecuta las migraciones y verifica la conexión.',
                    null,
                    'php artisan migrate --force'
                );
            } else {
                $ran = DB::table('migrations')->pluck('migration')->map(static fn ($value) => (string) $value)->all();
                $files = [];
                foreach (File::files(database_path('migrations')) as $file) {
                    $files[] = $file->getBasename('.php');
                }
                $pending = array_values(array_diff($files, $ran));

                $this->add(
                    $checks,
                    'db.migrations',
                    'Base de datos',
                    'Migraciones pendientes',
                    $pending === [] ? 'ok' : 'critical',
                    $pending === [] ? '0 pendientes' : count($pending) . ' pendientes',
                    $pending === []
                        ? 'El esquema está al día con los archivos de migración.'
                        : 'Faltan migraciones por aplicar: ' . implode(', ', array_slice($pending, 0, 8)),
                    $pending === [] ? null : 'Aplica las migraciones con respaldo previo y revisa el SQL.',
                    null,
                    $pending === [] ? null : 'php artisan migrate --force'
                );
            }
        } catch (Throwable $e) {
            $this->add(
                $checks,
                'db.migrations',
                'Base de datos',
                'Migraciones',
                'warning',
                'No verificable',
                'No se pudo comparar migraciones: ' . Str::limit($e->getMessage(), 180),
                'Revisa permisos y la tabla migrations.',
                null,
                null
            );
        }

        $this->checkJobsTable($checks);
        $this->checkDatabaseSize($checks);
        $this->checkRecordCounts($checks);
    }

    private function checkJobsTable(array &$checks): void
    {
        try {
            if (! Schema::hasTable('jobs')) {
                $this->add(
                    $checks,
                    'queue.jobs_table',
                    'Base de datos',
                    'Tabla de trabajos',
                    config('queue.default') === 'database' ? 'critical' : 'info',
                    'No existe',
                    'La cola configurada es ' . config('queue.default') . '.',
                    config('queue.default') === 'database' ? 'Crea la tabla jobs o cambia QUEUE_CONNECTION.' : null,
                    null,
                    config('queue.default') === 'database' ? 'php artisan queue:table && php artisan migrate' : null
                );
            } else {
                $pending = DB::table('jobs')->count();
                $this->add(
                    $checks,
                    'queue.pending',
                    'Base de datos',
                    'Trabajos en cola',
                    $pending > 100 ? 'warning' : 'ok',
                    (string) $pending,
                    $pending > 0
                        ? 'Hay trabajos esperando; confirma que el worker esté corriendo.'
                        : 'No hay trabajos pendientes en la cola.',
                    $pending > 100 ? 'Revisa el worker y el retraso de la cola.' : null,
                    null,
                    $pending > 100 ? 'php artisan queue:work --tries=3' : null
                );
            }

            if (Schema::hasTable('failed_jobs')) {
                $failed = DB::table('failed_jobs')->count();
                $this->add(
                    $checks,
                    'queue.failed',
                    'Base de datos',
                    'Trabajos fallidos',
                    $failed === 0 ? 'ok' : ($failed > 10 ? 'critical' : 'warning'),
                    (string) $failed,
                    $failed === 0
                        ? 'No hay trabajos fallidos registrados.'
                        : 'Hay trabajos que agotaron sus reintentos; revisa el error antes de reintentarlos.',
                    $failed === 0 ? null : 'Revisa con php artisan queue:failed y reintenta uno por uno.',
                    null,
                    $failed === 0 ? null : 'php artisan queue:failed'
                );
            }
        } catch (Throwable $e) {
            $this->add(
                $checks,
                'queue.check',
                'Base de datos',
                'Estado de la cola',
                'warning',
                'No verificable',
                Str::limit($e->getMessage(), 180),
                'Revisa la tabla jobs/failed_jobs y la conexión de cola.',
                null,
                null
            );
        }
    }

    private function checkDatabaseSize(array &$checks): void
    {
        try {
            $driver = DB::connection()->getDriverName();
            $size = null;

            if ($driver === 'pgsql') {
                $row = DB::selectOne('select pg_size_pretty(pg_database_size(current_database())) as size');
                $size = $row->size ?? null;
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                $row = DB::selectOne(
                    'select round(sum(data_length + index_length) / 1024 / 1024, 1) as size
                     from information_schema.tables where table_schema = database()'
                );
                $size = isset($row->size) ? $row->size . ' MB' : null;
            } elseif ($driver === 'sqlite') {
                $path = (string) config('database.connections.sqlite.database');
                $size = File::isFile($path) ? $this->formatBytes(File::size($path)) : null;
            }

            if ($size !== null) {
                $this->add(
                    $checks,
                    'db.size',
                    'Base de datos',
                    'Tamaño de la base',
                    'info',
                    (string) $size,
                    'Tamaño actual de la base de datos.',
                    null,
                    null,
                    null
                );
            }
        } catch (Throwable) {
            // Dato informativo: no afecta al estado.
        }
    }

    private function checkRecordCounts(array &$checks): void
    {
        $counts = [];

        foreach ([
            'users' => User::class,
            'allies' => Ally::class,
            'promotions' => Promotion::class,
        ] as $table => $model) {
            try {
                if (Schema::hasTable($table)) {
                    $counts[$table] = $model::query()->count();
                }
            } catch (Throwable) {
                // Sin dato.
            }
        }

        if ($counts !== []) {
            $this->add(
                $checks,
                'db.records',
                'Base de datos',
                'Registros principales',
                'info',
                collect($counts)->map(fn ($value, $key) => $key . ': ' . $value)->implode(' · '),
                'Conteo informativo de usuarios, aliados y promociones.',
                null,
                null,
                null
            );
        }
    }

    private function checkStorage(array &$checks): void
    {
        $paths = [
            storage_path() => 'storage',
            storage_path('logs') => 'storage/logs',
            storage_path('framework') => 'storage/framework',
            storage_path('framework/cache') => 'storage/framework/cache',
            storage_path('framework/sessions') => 'storage/framework/sessions',
            storage_path('framework/views') => 'storage/framework/views',
            storage_path('app/public') => 'storage/app/public',
            base_path('bootstrap/cache') => 'bootstrap/cache',
        ];

        $problems = [];
        foreach ($paths as $path => $label) {
            if (! is_dir($path)) {
                $problems[] = $label . ' (no existe)';
            } elseif (! $this->canWriteDirectory($path)) {
                $problems[] = $label . ' (sin escritura)';
            }
        }

        $this->add(
            $checks,
            'storage.writable',
            'Almacenamiento',
            'Directorios escribibles',
            $problems === [] ? 'ok' : 'critical',
            $problems === [] ? 'Todos OK' : count($problems) . ' con problemas',
            $problems === []
                ? 'Laravel puede escribir logs, caché, sesiones y vistas compiladas.'
                : 'Sin escritura fallan sesiones, caché, logs y compilación: ' . implode(', ', $problems),
            $problems === [] ? null : 'Corrige propietario/permisos: usuario forge y 775 para storage y bootstrap/cache.',
            null,
            $problems === [] ? null : 'sudo chown -R forge:forge storage bootstrap/cache && sudo chmod -R 775 storage bootstrap/cache'
        );

        try {
            $probe = storage_path('app/.audit-probe-' . Str::random(8));
            File::put($probe, 'ok');
            File::delete($probe);
            $writeOk = true;
        } catch (Throwable) {
            $writeOk = false;
        }

        $this->add(
            $checks,
            'storage.write_test',
            'Almacenamiento',
            'Prueba de escritura real',
            $writeOk ? 'ok' : 'critical',
            $writeOk ? 'Correcta' : 'Falló',
            $writeOk
                ? 'Se creó y eliminó un archivo temporal correctamente.'
                : 'No se pudo crear un archivo temporal en storage/app.',
            $writeOk ? null : 'Revisa permisos y espacio en disco.',
            null,
            null
        );

        $link = public_path('storage');
        $linkOk = is_link($link) && file_exists($link);
        $this->add(
            $checks,
            'storage.link',
            'Almacenamiento',
            'Enlace public/storage',
            $linkOk ? 'ok' : 'warning',
            $linkOk ? 'Correcto' : (is_dir($link) ? 'Directorio real (no enlace)' : 'No existe'),
            $linkOk
                ? 'Los archivos públicos de storage se sirven correctamente.'
                : 'Sin el enlace, las imágenes subidas a storage/app/public no se verán en la web.',
            $linkOk ? null : 'Crea el enlace con php artisan storage:link.',
            $linkOk ? null : 'storage_link',
            $linkOk ? null : 'php artisan storage:link'
        );

        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());
        $percent = ($free !== false && $total !== false && $total > 0) ? round(($free / $total) * 100, 1) : null;
        $diskStatus = $percent === null ? 'info' : ($percent < 5 ? 'critical' : ($percent < 15 ? 'warning' : 'ok'));
        $this->add(
            $checks,
            'storage.disk',
            'Almacenamiento',
            'Espacio en disco',
            $diskStatus,
            $percent === null ? 'No disponible' : $percent . '% libre · ' . $this->formatBytes($free) . ' de ' . $this->formatBytes($total),
            $diskStatus === 'ok'
                ? 'Hay espacio suficiente para logs, caché y subidas.'
                : 'El disco se está llenando; puede impedir subir archivos o escribir logs.',
            $diskStatus === 'ok' ? null : 'Libera espacio o amplía el disco del servidor.',
            null,
            null
        );

        $this->checkBackups($checks);
    }

    private function checkBackups(array &$checks): void
    {
        $latest = null;
        $files = [];

        foreach ((array) config('system_audit.backup_paths', []) as $configuredPath) {
            // Las rutas se guardan relativas para que `config:cache` no fije
            // la ruta absoluta de un release antiguo de Forge.
            $path = is_dir($configuredPath) ? $configuredPath : storage_path($configuredPath);

            if (! is_dir($path)) {
                continue;
            }

            foreach (File::glob($path . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    $files[] = $file;
                    $time = File::lastModified($file);
                    if ($latest === null || $time > $latest) {
                        $latest = $time;
                    }
                }
            }
        }

        if ($files === []) {
            $this->add(
                $checks,
                'backup.exists',
                'Almacenamiento',
                'Backups',
                'warning',
                'No se detectan respaldos',
                'No hay archivos de respaldo en las rutas habituales de storage.',
                'Configura un respaldo diario de la base de datos y de storage/app/public.',
                null,
                null
            );

            return;
        }

        $days = $latest !== null ? Carbon::createFromTimestamp($latest)->diffInDays(now()) : null;
        $status = $days !== null && $days > 7 ? 'warning' : 'ok';
        $this->add(
            $checks,
            'backup.latest',
            'Almacenamiento',
            'Último respaldo',
            $status,
            $latest !== null ? Carbon::createFromTimestamp($latest)->diffForHumans() : 'Disponible',
            $status === 'ok'
                ? 'Se detecta un respaldo reciente.'
                : 'El respaldo más reciente tiene más de una semana.',
            $status === 'ok' ? null : 'Programa un backup diario y verifica que se copie fuera del servidor.',
            null,
            null
        );
    }

    private function checkCacheQueueSessions(array &$checks): void
    {
        $key = 'system-audit:probe:' . Str::random(12);
        $cacheOk = false;
        $cacheError = null;

        try {
            Cache::put($key, 'ok', 15);
            $cacheOk = Cache::get($key) === 'ok';
            Cache::forget($key);
        } catch (Throwable $e) {
            $cacheError = $e->getMessage();
        }

        $this->add(
            $checks,
            'cache.store',
            'Caché y colas',
            'Caché operativa',
            $cacheOk ? 'ok' : 'critical',
            $cacheOk ? 'Lectura/escritura OK' : 'Falló',
            $cacheOk
                ? 'El almacén de caché responde correctamente.'
                : 'No se pudo leer/escribir en caché: ' . Str::limit((string) $cacheError, 180),
            $cacheOk ? null : 'Revisa CACHE_STORE, la tabla cache o Redis.',
            $cacheOk ? null : 'optimize_clear',
            $cacheOk ? null : 'php artisan cache:clear'
        );

        $cacheDriver = (string) config('cache.default');
        $this->add(
            $checks,
            'cache.driver',
            'Caché y colas',
            'Driver de caché',
            $cacheDriver === 'array' && app()->environment('production') ? 'warning' : 'info',
            $cacheDriver,
            $cacheDriver === 'array'
                ? 'El driver array no comparte caché entre peticiones; en producción es una mala elección.'
                : 'Driver de caché configurado.',
            $cacheDriver === 'array' ? 'Usa database, redis o file en producción.' : null,
            null,
            null
        );

        $this->add(
            $checks,
            'cache.config',
            'Caché y colas',
            'Configuración cacheada',
            'info',
            app()->configurationIsCached() ? 'Sí' : 'No',
            app()->configurationIsCached()
                ? 'La configuración está cacheada; cambia .env y ejecuta config:clear antes de volver a cachear.'
                : 'La configuración se lee del .env en cada petición.',
            null,
            null,
            null
        );

        $this->add(
            $checks,
            'cache.routes',
            'Caché y colas',
            'Rutas cacheadas',
            'info',
            app()->routesAreCached() ? 'Sí' : 'No',
            app()->routesAreCached()
                ? 'Las rutas están cacheadas; recuerda route:clear tras agregar rutas.'
                : 'Las rutas se resuelven dinámicamente.',
            null,
            null,
            null
        );

        $queue = (string) config('queue.default');
        $queueStatus = $queue === 'sync' && app()->environment('production') ? 'warning' : 'info';
        $this->add(
            $checks,
            'queue.driver',
            'Caché y colas',
            'Driver de cola',
            $queueStatus,
            $queue,
            $queue === 'sync'
                ? 'Con sync los jobs se ejecutan dentro de la petición y pueden agotar el tiempo.'
                : 'La cola se procesa de forma asíncrona.',
            $queueStatus === 'warning' ? 'Usa database o redis y mantén un worker activo.' : null,
            null,
            null
        );

        if (Schema::hasTable('jobs')) {
            try {
                $pending = DB::table('jobs')->count();
                if ($pending > 0) {
                    $this->add(
                        $checks,
                        'queue.health',
                        'Caché y colas',
                        'Cola con trabajos esperando',
                        $pending > 100 ? 'warning' : 'info',
                        (string) $pending,
                        'Hay trabajos pendientes; verifica que el worker esté activo.',
                        null,
                        null,
                        null
                    );
                }
            } catch (Throwable) {
                // Informativo.
            }
        }

        $sessionDriver = (string) config('session.driver');
        $sessionStatus = 'info';

        if ($sessionDriver === 'file' && ! is_writable((string) config('session.files'))) {
            $sessionStatus = 'critical';
        } elseif ($sessionDriver === 'database' && ! Schema::hasTable((string) config('session.table', 'sessions'))) {
            $sessionStatus = 'critical';
        } elseif ($sessionDriver === 'array') {
            $sessionStatus = 'warning';
        }

        $this->add(
            $checks,
            'session.driver',
            'Caché y colas',
            'Sesiones',
            $sessionStatus,
            $sessionDriver,
            $sessionStatus === 'ok' || $sessionStatus === 'info'
                ? 'El driver de sesión está configurado.'
                : 'El driver de sesión no puede escribir o le falta su tabla.',
            $sessionStatus === 'ok' || $sessionStatus === 'info' ? null : 'Revisa SESSION_DRIVER y sus permisos/tabla.',
            null,
            null
        );
    }

    private function checkSecurity(array &$checks): void
    {
        $isProduction = app()->environment('production');

        $this->add(
            $checks,
            'security.session_secure',
            'Seguridad',
            'Cookie de sesión segura',
            config('session.secure') ? 'ok' : ($isProduction ? 'warning' : 'info'),
            config('session.secure') ? 'Secure' : 'No Secure',
            config('session.secure')
                ? 'La cookie solo se envía por HTTPS.'
                : 'Sin Secure, la cookie podría viajar por HTTP.',
            config('session.secure') ? null : 'Activa SESSION_SECURE_COOKIE=true en producción.',
            null,
            null
        );

        $this->add(
            $checks,
            'security.session_httponly',
            'Seguridad',
            'Cookie HttpOnly',
            config('session.http_only') ? 'ok' : 'critical',
            config('session.http_only') ? 'Sí' : 'No',
            config('session.http_only')
                ? 'JavaScript no puede leer la cookie de sesión.'
                : 'Sin HttpOnly, un XSS puede robar la sesión.',
            config('session.http_only') ? null : 'Activa SESSION_HTTP_ONLY=true.',
            null,
            null
        );

        $sameSite = config('session.same_site');
        $this->add(
            $checks,
            'security.session_samesite',
            'Seguridad',
            'SameSite',
            $sameSite ? 'ok' : 'warning',
            $sameSite ? (string) $sameSite : 'Sin definir',
            $sameSite
                ? 'La cookie limita envíos desde otros sitios.'
                : 'SameSite ayuda a mitigar CSRF.',
            $sameSite ? null : 'Define SESSION_SAME_SITE=lax o strict.',
            null,
            null
        );

        $this->add(
            $checks,
            'security.session_encrypt',
            'Seguridad',
            'Sesión cifrada',
            config('session.encrypt') ? 'ok' : 'warning',
            config('session.encrypt') ? 'Sí' : 'No',
            config('session.encrypt')
                ? 'El contenido de la sesión se guarda cifrado.'
                : 'La sesión se guarda en claro; evita datos sensibles en ella.',
            config('session.encrypt') ? null : 'Activa SESSION_ENCRYPT=true.',
            null,
            null
        );

        try {
            $admins = User::query()
                ->where(function ($query) {
                    $query->where('role', 'admin')->orWhere('user_type', 'admin');
                })
                ->get(['id', 'email', 'two_factor_enabled']);

            $without2fa = $admins->filter(fn ($admin) => ! (bool) ($admin->two_factor_enabled ?? false))->count();

            $this->add(
                $checks,
                'security.admin_2fa',
                'Seguridad',
                'Administradores con 2FA',
                $admins->isEmpty() ? 'critical' : ($without2fa > 0 ? 'warning' : 'ok'),
                $admins->isEmpty() ? 'Sin administradores' : ($admins->count() - $without2fa) . '/' . $admins->count(),
                $admins->isEmpty()
                    ? 'No se encontraron usuarios con rol de administrador.'
                    : ($without2fa > 0
                        ? $without2fa . ' administrador(es) no tienen verificación en dos pasos.'
                        : 'Todos los administradores tienen 2FA activo.'),
                $without2fa > 0 ? 'Activa 2FA en Configuración para cada administrador.' : null,
                null,
                null
            );
        } catch (Throwable $e) {
            $this->add(
                $checks,
                'security.admin_2fa',
                'Seguridad',
                'Administradores con 2FA',
                'warning',
                'No verificable',
                Str::limit($e->getMessage(), 180),
                'Revisa la tabla users y el campo two_factor_enabled.',
                null,
                null
            );
        }

        $publicEnv = [];
        foreach ([public_path('.env'), public_path('.env.bak'), public_path('.env.save')] as $path) {
            if (is_file($path)) {
                $publicEnv[] = str_replace(public_path() . '/', '', $path);
            }
        }

        $this->add(
            $checks,
            'security.public_env',
            'Seguridad',
            '.env expuesto',
            $publicEnv === [] ? 'ok' : 'critical',
            $publicEnv === [] ? 'No expuesto' : implode(', ', $publicEnv),
            $publicEnv === []
                ? 'No hay archivos .env dentro de public.'
                : 'Un .env dentro de public puede descargarse con las claves del sistema.',
            $publicEnv === [] ? null : 'Mueve/elimina esos archivos de public inmediatamente.',
            null,
            null
        );

        $allowed = $this->allowedEmails();
        $this->add(
            $checks,
            'security.audit_access',
            'Seguridad',
            'Acceso a la auditoría',
            'info',
            $allowed === [] ? 'Todos los admins' : count($allowed) . ' correo(s)',
            $allowed === []
                ? 'Cualquier usuario con rol admin puede entrar. Define SYSTEM_AUDIT_EMAILS para restringirlo a tu correo.'
                : 'Solo los correos configurados en SYSTEM_AUDIT_EMAILS pueden entrar.',
            $allowed === [] ? 'Para que solo tú entres, define SYSTEM_AUDIT_EMAILS en el .env.' : null,
            null,
            null
        );

        $this->add(
            $checks,
            'security.headers',
            'Seguridad',
            'Cabeceras de seguridad',
            class_exists(\App\Http\Middleware\SecurityHeaders::class) ? 'ok' : 'warning',
            class_exists(\App\Http\Middleware\SecurityHeaders::class) ? 'Activas' : 'No detectadas',
            class_exists(\App\Http\Middleware\SecurityHeaders::class)
                ? 'El middleware de CSP/HSTS está registrado en el grupo web.'
                : 'No se encontró el middleware de cabeceras.',
            null,
            null,
            null
        );
    }

    private function checkIntegrations(array &$checks): void
    {
        $gemini = filled(config('gemini.api_key'));
        $this->add(
            $checks,
            'integrations.gemini',
            'Integraciones',
            'Gemini (IA)',
            $gemini ? 'ok' : 'warning',
            $gemini ? 'Clave configurada' : 'Sin clave',
            $gemini
                ? 'Hay una API key de Gemini configurada.'
                : 'Sin GEMINI_API_KEY el chat no podrá responder con IA.',
            $gemini ? null : 'Configura GEMINI_API_KEY y ejecuta config:clear.',
            $gemini ? 'test_ai' : null,
            null
        );

        $deepseek = filled(config('deepseek.api_key'));
        $this->add(
            $checks,
            'integrations.deepseek',
            'Integraciones',
            'DeepSeek (IA)',
            $deepseek ? 'ok' : 'warning',
            $deepseek ? 'Clave configurada' : 'Sin clave',
            $deepseek
                ? 'Hay una API key de DeepSeek configurada.'
                : 'Sin DEEPSEEK_API_KEY el respaldo de IA no está disponible.',
            $deepseek ? null : 'Configura DEEPSEEK_API_KEY y ejecuta config:clear.',
            null,
            null
        );

        $mailer = (string) config('mail.default');
        $mailStatus = in_array($mailer, ['log', 'array'], true) && app()->environment('production') ? 'warning' : 'ok';
        $this->add(
            $checks,
            'integrations.mail',
            'Integraciones',
            'Correo saliente',
            $mailStatus,
            $mailer . ' · ' . (string) config('mail.from.address'),
            $mailStatus === 'ok'
                ? 'El transporte de correo está configurado.'
                : 'Con log/array los correos no salen realmente al usuario.',
            $mailStatus === 'ok' ? null : 'Configura SMTP/Resend y prueba un correo real.',
            null,
            null
        );

        $vapid = filled(config('webpush.vapid.public_key')) && filled(config('webpush.vapid.private_key'));
        $this->add(
            $checks,
            'integrations.webpush',
            'Integraciones',
            'Web Push (VAPID)',
            $vapid ? 'ok' : 'warning',
            $vapid ? 'Claves configuradas' : 'Sin claves',
            $vapid
                ? 'Las notificaciones push del navegador pueden firmarse.'
                : 'Sin claves VAPID no se pueden enviar notificaciones push.',
            $vapid ? null : 'Genera claves con php artisan webpush:vapid y configura VAPID_*.',
            null,
            $vapid ? null : 'php artisan webpush:vapid'
        );

        $bncKeys = [
            'client_guid' => config('bnc.client_guid'),
            'master_key' => config('bnc.master_key'),
            'merchant_id' => config('bnc.merchant_id'),
        ];
        $bncMissing = array_keys(array_filter($bncKeys, static fn ($value) => blank($value)));
        $this->add(
            $checks,
            'integrations.bnc',
            'Integraciones',
            'BNC (pagos)',
            $bncMissing === [] ? 'ok' : 'warning',
            $bncMissing === [] ? 'Credenciales completas' : 'Faltan: ' . implode(', ', $bncMissing),
            $bncMissing === []
                ? 'Las credenciales base de BNC están configuradas.'
                : 'Sin estas credenciales fallan los pagos o la generación de archivos BNC.',
            $bncMissing === [] ? null : 'Completa BNC_* en el .env de producción.',
            null,
            null
        );

        $qrSecret = filled(config('services.qr.secret'));
        $this->add(
            $checks,
            'integrations.qr',
            'Integraciones',
            'Firma de QR',
            $qrSecret ? 'ok' : 'warning',
            $qrSecret ? 'Configurada' : 'Sin QR_SECRET_KEY',
            $qrSecret
                ? 'Los QR de pago se pueden firmar.'
                : 'Sin QR_SECRET_KEY el generador rechaza crear QRs.',
            $qrSecret ? null : 'Define QR_SECRET_KEY en el .env.',
            null,
            null
        );
    }

    private function checkLogs(array &$checks): void
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::isFile($logPath)) {
            $this->add(
                $checks,
                'logs.file',
                'Registros',
                'Log de Laravel',
                'info',
                'No existe',
                'Todavía no se ha escrito storage/logs/laravel.log.',
                null,
                null,
                null
            );

            return;
        }

        $size = File::size($logPath);
        $this->add(
            $checks,
            'logs.size',
            'Registros',
            'Tamaño del log',
            $size > 100 * 1024 * 1024 ? 'warning' : 'ok',
            $this->formatBytes($size),
            $size > 100 * 1024 * 1024
                ? 'El log ocupa más de 100 MB; puede llenar el disco y ralentizar la lectura.'
                : 'El tamaño del log es razonable.',
            $size > 100 * 1024 * 1024 ? 'Rota o limpia el log y configura LOG_DAILY.' : null,
            null,
            $size > 100 * 1024 * 1024 ? 'truncate -s 0 storage/logs/laravel.log' : null
        );

        $tail = $this->tail($logPath, max(32, (int) config('system_audit.log_tail_kb', 256)) * 1024);

        preg_match_all(
            '/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})\].*?\.(ERROR|CRITICAL|ALERT|EMERGENCY):/mi',
            $tail,
            $matches,
            PREG_SET_ORDER
        );

        $timestamps = [];
        $lastErrorAt = null;
        $lastErrorLine = null;

        foreach ($matches as $match) {
            try {
                $timestamp = Carbon::parse($match[1]);
            } catch (Throwable) {
                continue;
            }

            $timestamps[] = $timestamp;

            if ($lastErrorAt === null || $timestamp->greaterThan($lastErrorAt)) {
                $lastErrorAt = $timestamp;
            }
        }

        $errors = count($matches);
        $lastError = $lastErrorAt
            ? $lastErrorAt->format('d/m/Y H:i:s') . ' (' . $lastErrorAt->diffForHumans() . ')'
            : null;

        if ($errors > 0 && preg_match('/^\[.*?\].*?\.(ERROR|CRITICAL|ALERT|EMERGENCY): (.*)$/mi', $tail, $lineMatch)) {
            $lastErrorLine = Str::limit(trim($lineMatch[2]), 140);
        }

        $since = $this->previousRunAt ?? now()->subDay();
        $newErrors = 0;
        $last24h = 0;

        foreach ($timestamps as $timestamp) {
            if ($timestamp->greaterThanOrEqualTo($since)) {
                $newErrors++;
            }

            if ($timestamp->greaterThanOrEqualTo(now()->subDay())) {
                $last24h++;
            }
        }

        $status = $newErrors === 0 ? 'ok' : ($newErrors > 20 ? 'critical' : 'warning');

        $detail = $newErrors === 0
            ? 'No hay errores nuevos desde la última ejecución. Los errores anteriores siguen en el archivo, pero ya no cuentan como nuevos.'
            : $newErrors . ' error(es) nuevo(s) desde la última ejecución.';

        if ($lastError) {
            $detail .= ($newErrors === 0 ? ' Último error histórico: ' : ' Último error del archivo: ') . $lastError . '.';
        }

        if ($lastErrorLine) {
            $detail .= ' ' . $lastErrorLine;
        }

        $this->add(
            $checks,
            'logs.errors',
            'Registros',
            'Errores nuevos',
            $status,
            $newErrors . ' nuevos · ' . $last24h . ' en 24 h · ' . $errors . ' en el archivo',
            $detail,
            $status === 'ok'
                ? null
                : 'Revisa el stack trace completo en storage/logs/laravel.log.',
            null,
            $status === 'ok' ? null : 'tail -n 200 storage/logs/laravel.log'
        );

        $this->add(
            $checks,
            'logs.modified',
            'Registros',
            'Última escritura del log',
            'info',
            Carbon::createFromTimestamp(File::lastModified($logPath))->diffForHumans(),
            'Actividad más reciente registrada en el log.',
            null,
            null,
            null
        );
    }

    private function checkPerformance(array &$checks): void
    {
        $isProduction = app()->environment('production');
        $configCached = app()->configurationIsCached();
        $routesCached = app()->routesAreCached();

        $this->add(
            $checks,
            'performance.config_cache',
            'Rendimiento',
            'Configuración cacheada',
            $isProduction && ! $configCached ? 'warning' : 'ok',
            $configCached ? 'Sí' : 'No',
            $configCached
                ? 'La configuración está cacheada.'
                : 'En producción conviene cachear la configuración para evitar leer .env en cada petición.',
            $isProduction && ! $configCached ? 'Ejecuta php artisan config:cache después de cambiar .env.' : null,
            null,
            $isProduction && ! $configCached ? 'php artisan config:cache' : null
        );

        $this->add(
            $checks,
            'performance.route_cache',
            'Rendimiento',
            'Rutas cacheadas',
            $isProduction && ! $routesCached ? 'warning' : 'ok',
            $routesCached ? 'Sí' : 'No',
            $routesCached
                ? 'Las rutas están cacheadas.'
                : 'En producción conviene cachear las rutas para acelerar el arranque.',
            $isProduction && ! $routesCached ? 'Ejecuta php artisan route:cache después de verificar que no haya closures.' : null,
            null,
            $isProduction && ! $routesCached ? 'php artisan route:cache' : null
        );

        $opcache = function_exists('opcache_get_status') && (bool) @opcache_get_status(false);
        $this->add(
            $checks,
            'performance.opcache',
            'Rendimiento',
            'OPcache',
            $opcache ? 'ok' : 'warning',
            $opcache ? 'Activo' : 'Inactivo',
            $opcache
                ? 'PHP está cacheando el bytecode de la aplicación.'
                : 'Sin OPcache cada petición vuelve a compilar los archivos PHP.',
            $opcache ? null : 'Activa OPcache en PHP-FPM.',
            null,
            null
        );

        $this->add(
            $checks,
            'performance.memory_usage',
            'Rendimiento',
            'Memoria de esta petición',
            'info',
            $this->formatBytes(memory_get_peak_usage(true)),
            'Pico de memoria usado por la auditoría.',
            null,
            null,
            null
        );

        try {
            $this->add(
                $checks,
                'performance.routes',
                'Rendimiento',
                'Rutas registradas',
                'info',
                (string) count(Route::getRoutes()),
                'Cantidad de rutas cargadas por Laravel.',
                null,
                null,
                null
            );
        } catch (Throwable) {
            // Informativo.
        }

        $compiledViews = count(File::glob(storage_path('framework/views/*.php')) ?: []);
        $this->add(
            $checks,
            'performance.views',
            'Rendimiento',
            'Vistas compiladas',
            'info',
            (string) $compiledViews,
            'Vistas Blade compiladas disponibles en storage/framework/views.',
            null,
            null,
            null
        );
    }

    private function summarize(array $checks): array
    {
        $counts = ['ok' => 0, 'warning' => 0, 'critical' => 0, 'info' => 0];
        foreach ($checks as $check) {
            $status = $check['status'] ?? 'info';
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        $total = $counts['ok'] + $counts['warning'] + $counts['critical'];
        $score = $total > 0
            ? (int) round((($counts['ok'] * 100) + ($counts['warning'] * 65)) / $total)
            : 100;

        $overall = $counts['critical'] > 0 ? 'critical' : ($counts['warning'] > 0 ? 'warning' : 'ok');

        return [
            'overall' => $overall,
            'score' => max(0, min(100, $score)),
            'ok' => $counts['ok'],
            'warning' => $counts['warning'],
            'critical' => $counts['critical'],
            'info' => $counts['info'],
            'total' => $total,
        ];
    }

    private function buildRecommendations(array $checks): array
    {
        $priority = ['critical' => 0, 'warning' => 1, 'info' => 2];
        $items = [];

        foreach ($checks as $check) {
            if (($check['status'] ?? 'info') === 'ok' || ($check['status'] ?? 'info') === 'info') {
                continue;
            }

            $items[] = [
                'title' => $check['title'] ?? 'Revisar',
                'status' => $check['status'],
                'group' => $check['group'] ?? 'Sistema',
                'text' => $check['recommendation'] ?: $check['detail'] ?: 'Revisa esta comprobación.',
                'command' => $check['command'] ?? null,
            ];
        }

        usort($items, static function (array $a, array $b) use ($priority): int {
            return ($priority[$a['status']] ?? 9) <=> ($priority[$b['status']] ?? 9);
        });

        return $items;
    }

    private function add(
        array &$checks,
        string $id,
        string $group,
        string $title,
        string $status,
        string $value,
        string $detail,
        ?string $recommendation = null,
        ?string $fix = null,
        ?string $command = null
    ): void {
        if (! in_array($status, ['ok', 'warning', 'critical', 'info'], true)) {
            $status = 'info';
        }

        if ($fix !== null && ! array_key_exists($fix, self::FIX_ACTIONS)) {
            $fix = null;
        }

        $checks[] = [
            'id' => $id,
            'group' => $group,
            'title' => $title,
            'status' => $status,
            'value' => $value,
            'detail' => $detail,
            'recommendation' => $recommendation,
            'fix' => $fix,
            'command' => $command,
        ];
    }

    private function store(array $result): void
    {
        try {
            $directory = storage_path('app/system-audit');
            File::ensureDirectoryExists($directory);

            File::put(
                $directory . '/last.json',
                json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)
            );

            $history = $this->history();
            array_unshift($history, [
                'generated_at' => $result['generated_at'],
                'generated_by' => $result['generated_by'],
                'summary' => $result['summary'],
            ]);
            $history = array_slice($history, 0, max(1, (int) config('system_audit.history_limit', 15)));

            File::put(
                $directory . '/history.json',
                json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)
            );
        } catch (Throwable $e) {
            Log::warning('system_audit.store_failed', ['error' => $e->getMessage()]);
        }
    }

    private function canWriteDirectory(string $path): bool
    {
        if (! is_dir($path)) {
            return false;
        }

        $probe = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.audit-probe-' . Str::random(8);

        try {
            $written = File::put($probe, 'ok');
            $exists = File::exists($probe);
            File::delete($probe);

            if ($written === false || ! $exists) {
                return false;
            }

            return true;
        } catch (Throwable) {
            // En Windows is_writable() puede dar falsos negativos con ACL;
            // se usa como respaldo solo si la prueba real falló.
            return is_writable($path);
        }
    }

    private function storagePath(string $file): string
    {
        return storage_path('app/system-audit/' . $file);
    }

    private function testAi(): array
    {
        $lines = [];

        try {
            $gemini = app(\App\Services\GeminiService::class)->ping();
            $lines[] = 'Gemini: ' . ($gemini['ok']
                ? 'OK (' . ($gemini['model'] ?? 'modelo') . ')'
                : 'FALLÓ (' . Str::limit((string) ($gemini['error'] ?? 'sin detalle'), 120) . ')');
        } catch (Throwable $e) {
            $lines[] = 'Gemini: FALLÓ (' . Str::limit($e->getMessage(), 120) . ')';
        }

        try {
            $reply = app(\App\Services\DeepSeekService::class)->chat('Responde solo con OK.');
            $text = trim((string) ($reply['respuesta'] ?? ''));
            $lines[] = 'DeepSeek: ' . ($text !== '' ? 'OK' : 'FALLÓ (respuesta vacía)');
        } catch (Throwable $e) {
            $lines[] = 'DeepSeek: FALLÓ (' . Str::limit($e->getMessage(), 120) . ')';
        }

        return [
            'ok' => ! collect($lines)->contains(fn ($line) => str_contains($line, 'FALLÓ')),
            'message' => 'Prueba de IA completada.',
            'output' => implode("\n", $lines),
        ];
    }

    private function tail(string $path, int $bytes): string
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return '';
        }

        try {
            $size = filesize($path) ?: 0;
            $offset = max(0, $size - $bytes);
            fseek($handle, $offset);
            $contents = stream_get_contents($handle);

            return $contents === false ? '' : $contents;
        } finally {
            fclose($handle);
        }
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function formatBytes(int|float|null $bytes): string
    {
        if ($bytes === null) {
            return '—';
        }

        $size = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return number_format($size, $size >= 10 ? 0 : 1) . ' ' . $units[$index];
    }
}
