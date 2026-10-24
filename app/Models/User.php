<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Descomenta si necesitas verificación de email
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // Para gestionar roles y permisos
use Illuminate\Support\Facades\Storage; // Necesario para la URL de la foto de perfil
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Para la relación uno a uno con Ally

class User extends Authenticatable // Quita `implements MustVerifyEmail` si no lo necesitas
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // Este podría ser un nombre corto o nombre de usuario
        'firstname',
        'lastname',
        'email',
        'password',
        'identification',
        'full_name', // Nombre completo del usuario
        'dob', // Fecha de nacimiento
        'phone1',
        'phone2',
        'address',
        'profile_photo_path', // Ruta de la foto de perfil en el storage
        'role', // Si gestionas roles de forma interna (además de Spatie)
        'user_type', // Tipo de usuario (e.g., 'Principal', 'Secundario')
        'two_factor_enabled',
        'last_login_at', // Último inicio de sesión
        'is_ally', // Indica si el usuario tiene un perfil de aliado
        'status',           // ¡Añadido!
        'registration_date',// ¡Añadido!
        'notes',
        'notifications_enabled',
        'push_enabled',
        'dark_mode_enabled',
        'two_factor_secret',
        'two_factor_enabled',

        // Métodos de pago del perfil
        'payment_bank_code',
        'payment_bank_name',
        'payment_phone',
        'payment_holder_name',
        'payment_holder_id',
        'payment_debit_bank_code',
        'payment_debit_bank_name',
        'payment_debit_account',
        'payment_debit_account_type',
        'payment_card_last4',
        'payment_card_expiry',
        'payment_card_holder',
        'payment_updated_at',

        // Campos exactos que consume la API BNC
        'debtor_cell_phone',
        'debtor_id',
        'card_number',
        'card_expiration',
        'card_holder_name',
        'card_holder_id',
        'card_type_id',
        'card_account_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret', // Descomenta si usas 2FA con Laravel Fortify
        // 'two_factor_recovery_codes', // Descomenta si usas 2FA con Laravel Fortify
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Laravel 10+ hashea automáticamente
            'dob' => 'date',         // Castea la fecha de nacimiento a objeto Carbon
            'last_login_at' => 'datetime', // Castea el último login a objeto Carbon
            'is_ally' => 'boolean',  // Castea a booleano
            'two_factor_enabled' => 'boolean', // Castea a booleano
            'notifications_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'dark_mode_enabled' => 'boolean',
            'payment_debit_account' => 'encrypted',
            'payment_updated_at' => 'datetime',
            'debtor_account' => 'encrypted',
            'debtor_cell_phone' => 'encrypted',
            'debtor_id' => 'encrypted',
            'card_number' => 'encrypted',
            'card_expiration' => 'encrypted',
            'card_holder_name' => 'encrypted',
            'card_holder_id' => 'encrypted',
            'card_account_type' => 'integer',
            'card_type_id' => 'integer',
        ];
    }

    // --- Accessors ---

    /**
     * Accesor para la URL de la foto de perfil.
     * Genera la URL pública si existe una ruta de foto, de lo contrario, un avatar por defecto.
     *
     * @return string
     */
    // Nuevo Accessor para la URL de la foto de perfil
    public function getProfilePhotoUrlAttribute()
    {
        // Verifica si el usuario tiene una ruta de foto de perfil y si el archivo existe
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            // Asume que 'profile_photo_path' guarda algo como 'avatars/nombre-de-imagen.jpg'
            return Storage::disk('public')->url($this->profile_photo_path);
        }

        // Si no hay foto o no existe, devuelve una imagen por defecto
        return asset('assets/img/logos/usuario.png');
    }

    // --- Relaciones Eloquent ---

    /**
     * Define la relación uno a uno con el modelo Ally.
     * Un usuario *puede* tener un perfil de aliado asociado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ally(): HasOne
    {
        return $this->hasOne(Ally::class);
    }

    /**
     * Descuentos activados por el usuario.
     *
     * La usa GeminiService (User::with(['ally','activations'])) para armar el
     * contexto del asistente: sin esta relación el chat lanzaba
     * "Call to undefined relationship [activations]".
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activations(): HasMany
    {
        return $this->hasMany(DiscountActivation::class, 'user_id');
    }

    /**
     * Dispositivos suscritos a las notificaciones Web Push.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * ¿El usuario quiere recibir las notificaciones del panel como push?
     */
    public function wantsPushNotifications(): bool
    {
        return (bool) $this->push_enabled && (bool) $this->notifications_enabled;
    }
}
