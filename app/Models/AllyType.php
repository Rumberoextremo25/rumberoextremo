<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    // Si tu tabla es 'ally_types', Laravel lo inferirá automáticamente.
    // Si fuera un nombre diferente, lo especificarías: protected $table = 'nombre_de_tabla';
}
