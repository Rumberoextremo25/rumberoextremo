<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Descomenta si necesitas verificación de email
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Para gestionar roles y permisos
use Illuminate\Support\Facades\Storage; // Necesario para la URL de la foto de perfil
use Illuminate\Database\Eloquent\Relations\HasOne; // Para la relación uno a uno con Ally

class User extends Authenticatable // Quita `implements MustVerifyEmail` si no lo necesitas
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // 'two_factor_secret', // Descomenta si usas 2FA con Laravel Fortify
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
        ];
    }

    // --- Accessors ---

    /**
     * Accesor para la URL de la foto de perfil.
     * Genera la URL pública si existe una ruta de foto, de lo contrario, un avatar por defecto.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            // Asegúrate de que tu disco 'public' esté vinculado: php artisan storage:link
            // La importación de la fachada Storage es CRÍTICA aquí.
            return Storage::disk('public')->url($this->profile_photo_path);
        }

        // Genera un avatar con ui-avatars.com usando el nombre del usuario
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=FFFFFF&background=FF4B4B';
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
}
