<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Registra la actividad del panel y la reparte a la bandeja de notificaciones
 * de todos los administradores.
 *
 * Se escribe dos veces a propósito:
 *  - activity_logs: auditoría global (la usa el dashboard).
 *  - notifications:  bandeja por usuario, con estado leído/no leído.
 */
class AdminActivityNotifier
{
    /**
     * Configuración por modelo: etiqueta, atributo con el nombre visible,
     * ruta de edición, icono y color del badge.
     *
     * @var array<class-string, array<string, string>>
     */
    public const ENTITIES = [
        User::class => [
            'key' => 'usuario',
            'label' => 'Usuario',
            'name' => 'name',
            'route' => 'admin.users.edit',
            'icon' => 'fa-solid fa-user',
            'color' => 'primary',
        ],
        \App\Models\Ally::class => [
            'key' => 'aliado',
            'label' => 'Aliado',
            'name' => 'company_name',
            'route' => 'admin.aliados.edit',
            'icon' => 'fa-solid fa-handshake',
            'color' => 'info',
        ],
        \App\Models\CommercialAlly::class => [
            'key' => 'aliado_comercial',
            'label' => 'Aliado comercial',
            'name' => 'name',
            'route' => 'admin.commercial-allies.edit',
            'icon' => 'fa-solid fa-store',
            'color' => 'success',
        ],
        \App\Models\Promotion::class => [
            'key' => 'promocion',
            'label' => 'Promoción',
            'name' => 'title',
            'route' => 'admin.promotions.edit',
            'icon' => 'fa-solid fa-tags',
            'color' => 'warning',
        ],
        \App\Models\Banner::class => [
            'key' => 'banner',
            'label' => 'Banner',
            'name' => 'title',
            'route' => 'admin.banners.edit',
            'icon' => 'fa-solid fa-images',
            'color' => 'secondary',
        ],
    ];

    /**
     * Atributos que nunca generan notificación (ruido o datos sensibles).
     *
     * @var array<int, string>
     */
    private const IGNORED_ATTRIBUTES = [
        'updated_at',
        'created_at',
        'remember_token',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'last_login_at',
        // Preferencias personales: cambiarlas no es una "modificación" del panel
        'dark_mode_enabled',
        'notifications_enabled',
        'push_enabled',
        'profile_photo_path',
    ];

    /**
     * Nombres legibles para los campos más habituales.
     *
     * @var array<string, string>
     */
    private const ATTRIBUTE_LABELS = [
        'name' => 'Nombre',
        'firstname' => 'Nombres',
        'lastname' => 'Apellidos',
        'full_name' => 'Nombre completo',
        'email' => 'Correo',
        'role' => 'Rol',
        'user_type' => 'Tipo de usuario',
        'status' => 'Estado',
        'identification' => 'Identificación',
        'phone1' => 'Teléfono',
        'phone2' => 'Teléfono alterno',
        'address' => 'Dirección',
        'company_name' => 'Empresa',
        'company_rif' => 'RIF',
        'contact_person_name' => 'Contacto',
        'contact_email' => 'Correo de contacto',
        'contact_phone' => 'Teléfono de contacto',
        'company_address' => 'Dirección de la empresa',
        'website_url' => 'Sitio web',
        'discount' => 'Descuento',
        'notes' => 'Notas',
        'category_id' => 'Categoría',
        'sub_category_id' => 'Subcategoría',
        'business_type_id' => 'Tipo de negocio',
        'bank_name' => 'Banco',
        'account_number' => 'Número de cuenta',
        'account_type' => 'Tipo de cuenta',
        'account_holder_name' => 'Titular',
        'description' => 'Descripción',
        'image_url' => 'Imagen',
        'commission_percentage' => '% comisión',
        'default_payment_method' => 'Método de pago',
        'company' => 'Empresa',
        'title' => 'Título',
        'price' => 'Precio',
        'expires_at' => 'Vence',
        'max_uses' => 'Usos máximos',
        'current_uses' => 'Usos actuales',
        'terms_conditions' => 'Términos',
        'ally_id' => 'Aliado',
        'target_url' => 'URL de destino',
        'order' => 'Orden',
        'is_active' => 'Activo',
        'rating' => 'Valoración',
        'logo_url' => 'Logo',
        'registration_date' => 'Fecha de registro',
        'notifications_enabled' => 'Notificaciones',
        'two_factor_enabled' => '2FA',
    ];

    /**
     * Registra un evento y notifica a los administradores.
     *
     * @param  string  $action  created|updated|deleted
     * @param  array<int, array{label: string, from: string, to: string}>  $changes
     */
    public function record(string $action, Model $model, array $changes = []): void
    {
        $config = self::ENTITIES[$model::class] ?? null;

        if ($config === null) {
            return;
        }

        // Una edición sin cambios relevantes no genera notificación
        if ($action === 'updated' && $changes === []) {
            return;
        }

        $payload = $this->payload($action, $model, $config, $changes);

        try {
            ActivityLog::create([
                'activity_type' => $config['key'] . '.' . $action,
                'description' => $payload['title'] . ': ' . $payload['subject'],
                'user_id' => $this->ownerId($model),
                'performed_by' => $payload['actor_id'],
                'status' => 'success',
                'data' => [
                    'entity' => $config['key'],
                    'entity_id' => $model->getKey(),
                    'action' => $action,
                    'changes' => $changes,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('activity_log.write_failed', ['error' => $e->getMessage()]);
        }

        $this->notifyAdmins($payload);
    }

    /**
     * Construye el contenido que verá el administrador.
     *
     * @param  array<string, string>  $config
     * @param  array<int, array{label: string, from: string, to: string}>  $changes
     * @return array<string, mixed>
     */
    private function payload(string $action, Model $model, array $config, array $changes): array
    {
        $subject = $this->subjectName($model, $config);
        $actor = Auth::user();
        $verb = match ($action) {
            'created' => 'creado',
            'updated' => 'actualizado',
            'deleted' => 'eliminado',
            default => $action,
        };

        $title = match ($action) {
            'created' => $config['label'] . ' creado',
            'updated' => $config['label'] . ' actualizado',
            'deleted' => $config['label'] . ' eliminado',
            default => $config['label'] . ' modificado',
        };

        // Las entidades femeninas suenan mejor así
        if (in_array($config['key'], ['promocion'], true)) {
            $title = str_replace(['creado', 'actualizado', 'eliminado'], ['creada', 'actualizada', 'eliminada'], $title);
        }

        $message = $subject;
        if ($changes !== []) {
            $message .= ' · ' . implode(' · ', array_map(
                static fn (array $c) => $c['label'] . ': ' . $c['from'] . ' → ' . $c['to'],
                array_slice($changes, 0, 3)
            ));
        }
        if ($actor) {
            $message .= ' · por ' . $actor->name;
        }

        return [
            'action' => $action,
            'entity' => $config['key'],
            'entity_label' => $config['label'],
            'entity_id' => $model->getKey(),
            'title' => $title,
            'subject' => $subject,
            'message' => $message,
            'changes' => $changes,
            'icon' => $config['icon'],
            'color' => $config['color'],
            'url' => $this->url($action, $model, $config),
            'actor_id' => $actor?->getAuthIdentifier(),
            'actor_name' => $actor?->name,
        ];
    }

    /**
     * Nombre visible del registro afectado.
     *
     * @param  array<string, string>  $config
     */
    private function subjectName(Model $model, array $config): string
    {
        $value = $model->getAttribute($config['name']);

        if (blank($value)) {
            $value = $model->getAttribute('email') ?? ('#' . $model->getKey());
        }

        return \Illuminate\Support\Str::limit((string) $value, 60);
    }

    /**
     * Enlace al registro afectado (o al listado si se eliminó).
     *
     * @param  array<string, string>  $config
     */
    private function url(string $action, Model $model, array $config): ?string
    {
        try {
            if ($action === 'deleted' || ! \Illuminate\Support\Facades\Route::has($config['route'])) {
                return match ($config['key']) {
                    'usuario' => route('admin.users.index'),
                    'aliado' => route('admin.aliados.index'),
                    'aliado_comercial' => route('admin.commercial-allies.index'),
                    'promocion' => route('admin.promotions.index'),
                    'banner' => route('admin.banners.index'),
                    default => null,
                };
            }

            return route($config['route'], $model->getKey());
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Usuario "dueño" del registro (para el log de auditoría).
     */
    private function ownerId(Model $model): ?int
    {
        $candidate = $model->getAttribute('user_id');

        if (! $candidate && $model instanceof User) {
            $candidate = $model->getKey();
        }

        return $candidate ? (int) $candidate : null;
    }

    /**
     * Copia la notificación a la bandeja de cada administrador activo.
     *
     * @param  array<string, mixed>  $payload
     */
    private function notifyAdmins(array $payload): void
    {
        try {
            $admins = User::query()
                ->where(function ($query) {
                    $query->where('role', 'admin')->orWhere('user_type', 'admin');
                })
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new AdminActivityNotification($payload));

                // Push al navegador del móvil/escritorio (solo si lo activó)
                if ($admin->wantsPushNotifications()) {
                    $this->sendPush($admin, $payload);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('notifications.dispatch_failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Envía el push después de responder al navegador (no añade latencia).
     *
     * @param  array<string, mixed>  $payload
     */
    private function sendPush(User $admin, array $payload): void
    {
        $message = [
            'title' => $payload['title'],
            'body' => $payload['message'],
            'url' => $payload['url'] ?? route('admin.notifications.index'),
            'tag' => 'rumbero-' . ($payload['entity'] ?? 'panel'),
        ];

        $dispatch = function () use ($admin, $message) {
            try {
                app(\App\Services\WebPushService::class)->sendToUser($admin, $message);
            } catch (\Throwable $e) {
                Log::warning('webpush.deferred_failed', ['error' => $e->getMessage()]);
            }
        };

        if (function_exists('defer')) {
            defer($dispatch);
        } else {
            $dispatch();
        }
    }

    /**
     * Diferencias legibles entre el estado anterior y el nuevo.
     *
     * @param  array<string, mixed>  $before  Copia de los atributos originales
     * @return array<int, array{label: string, from: string, to: string}>
     */
    public static function describeChanges(Model $model, array $before): array
    {
        $changes = [];

        foreach ($model->getChanges() as $attribute => $newValue) {
            // Los cambios de contraseña se avisan, pero nunca se muestra el valor
            if ($attribute === 'password') {
                $changes[] = ['label' => 'Contraseña', 'from' => '••••••', 'to' => 'actualizada'];

                continue;
            }

            if (in_array($attribute, self::IGNORED_ATTRIBUTES, true)) {
                continue;
            }

            $oldValue = $before[$attribute] ?? null;

            if (self::stringify($oldValue) === self::stringify($newValue)) {
                continue;
            }

            $changes[] = [
                'label' => self::ATTRIBUTE_LABELS[$attribute] ?? ucfirst(str_replace('_', ' ', $attribute)),
                'from' => self::stringify($oldValue),
                'to' => self::stringify($newValue),
            ];
        }

        return $changes;
    }

    /**
     * Representación corta y segura de un valor.
     */
    private static function stringify(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if (is_array($value)) {
            return \Illuminate\Support\Str::limit(json_encode($value, JSON_UNESCAPED_UNICODE) ?: '—', 40);
        }

        return \Illuminate\Support\Str::limit((string) $value, 40);
    }
}
