<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; // ¡Importa la clase Str!

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    // Asegúrate de que 'slug' esté en el fillable
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Una categoría puede tener muchas subcategorías.
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }

    /**
     * Una categoría puede tener muchos aliados.
     */
    public function allies(): HasMany
    {
        return $this->hasMany(Ally::class);
    }

    /**
     * Mutador para el atributo 'name'.
     * Se ejecuta cada vez que se establece el 'name' de una categoría.
     * Genera automáticamente el 'slug' a partir del 'name'.
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value); // Genera un slug amigable
    }
}
