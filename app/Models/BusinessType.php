<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; // Importar la clase Str

class BusinessType extends Model
{
    use HasFactory;

    protected $table = 'business_types';
    // Añade 'slug' a los campos asignables masivamente
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Un tipo de negocio puede tener muchos aliados.
     */
    public function allies(): HasMany
    {
        return $this->hasMany(Ally::class);
    }

    // --- Mutator para generar el slug automáticamente ---
    // Se ejecuta cada vez que el atributo 'name' es seteado.
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value); // Genera el slug a partir del nombre
    }
}