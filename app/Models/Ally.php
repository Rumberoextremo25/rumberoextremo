<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar la relación BelongsTo

class Ally extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     * Por defecto es el nombre del modelo en plural (allies), pero es buena práctica especificarlo.
     *
     * @var string
     */
    protected $table = 'allies';

    /**
     * Los atributos que son asignables masivamente.
     * Estos campos corresponden directamente a las columnas de la tabla `allies`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // Clave foránea que conecta al usuario
        'company_name', // Renombrado de 'name' para mayor claridad
        'company_rif', // Renombrado de 'rif' para mayor claridad
        'service_category', // Renombrado de 'type' para mayor claridad
        'contact_person_name', // Renombrado de 'contact_person' para mayor claridad
        'contact_email',
        'contact_phone', // Renombrado de 'phone'
        'contact_phone_alt', // Renombrado de 'phone_alt'
        'company_address', // Renombrado de 'address'
        'website_url', // Renombrado de 'website'
        'discount', // Renombrado de 'discount_offer'
        'notes',
        'registered_at', // Renombrado de 'registration_date'
        'status',
    ];

    /**
     * Los atributos que deberían ser casteados a tipos nativos de PHP.
     * Esto facilita el manejo de fechas, números y otros tipos de datos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registered_at' => 'datetime', // Aseguramos que se castee a un objeto Carbon
        'discount' => 'float',         // Casteamos el descuento como flotante si puede tener decimales
    ];

    // Si tu clave primaria no es 'id' o no es auto-incremental, defínela aquí.
    // protected $primaryKey = 'ally_id';
    // public $incrementing = false;
    // protected $keyType = 'string';

    // --- Relaciones Eloquent ---

    /**
     * Define la relación inversa con el modelo User.
     * Un perfil de aliado pertenece a un único usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
