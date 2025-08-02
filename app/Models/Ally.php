<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ally extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada en la base de datos.
    protected $table = 'allies';

    // Atributos que se pueden asignar masivamente (mass assignable).
    // Estos campos corresponden a las columnas de tu tabla 'allies'.
    protected $fillable = [
        'id',
        'user_id',
        'company_name',
        'company_rif',
        'contact_person_name',
        'contact_email',
        'contact_phone',
        'contact_phone_alt',
        'company_address',
        'website_url',
        'discount',
        'notes',
        'registered_at',
        'status',
        'category_id',
        'sub_category_id',
        'business_type_id',
        'bank_name',
        'account_number',
        'account_type',
        'id_number',
        'account_holder_name',
    ];

    // --- Definición de las Relaciones del Modelo ---

    /**
     * Un aliado pertenece a una categoría.
     * Permite acceder a los detalles de la categoría a la que pertenece el aliado.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Un aliado puede pertenecer a una subcategoría (es opcional).
     * Permite acceder a los detalles de la subcategoría del aliado.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    /**
     * Un aliado pertenece a un tipo de negocio específico.
     * Permite acceder a los detalles del tipo de negocio del aliado.
     */
    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    /**
     * Un aliado está asociado con un usuario en el sistema.
     * Permite acceder a los detalles del usuario que gestiona o es el representante del aliado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Casteo de Atributos ---
    // Define cómo ciertos atributos deben ser convertidos al ser accedidos o guardados.
    protected $casts = [
        'registered_at' => 'datetime', // Convierte el campo 'registered_at' a un objeto Carbon (fecha y hora).
        'discount' => 'string',        // Asegura que el campo 'discount' se maneje como un string.
    ];

    // Aquí puedes añadir cualquier otro método, accesor, mutador o lógica de negocio
    // que sea relevante para el modelo Ally.
}