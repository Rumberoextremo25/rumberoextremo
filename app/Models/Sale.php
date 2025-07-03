<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ventas'; // Asegúrate de que esto coincida con el nombre de tu tabla de ventas

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'total', // Asume que tienes una columna 'total' para el monto de la venta
        'sale_date', // Asume que tienes una columna 'sale_date' para la fecha de la venta
        // Agrega otras columnas de tu tabla 'ventas' aquí
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sale_date' => 'datetime', // Castea la fecha de venta a un objeto Carbon
    ];
}
