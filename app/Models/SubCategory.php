<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; // ¡Importa la clase Str aquí también!

class SubCategory extends Model
{
    use HasFactory;

    protected $table = 'sub_categories';
    // Asegúrate de que 'slug' esté en el fillable
    protected $fillable = ['category_id', 'name', 'slug', 'description'];

    /**
     * Una subcategoría pertenece a una categoría.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Una subcategoría puede tener muchos aliados.
     */
    public function allies(): HasMany
    {
        return $this->hasMany(Ally::class);
    }

    /**
     * Mutador para el atributo 'name'.
     * Se ejecuta cada vez que se establece el 'name' de una subcategoría.
     * Genera automáticamente el 'slug' a partir del 'name'.
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value); // Genera un slug amigable
    }
}