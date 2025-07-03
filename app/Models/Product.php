<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ally_name',
        'description',
        'base_price',
        'discount_percentage',
        'final_price', // Asegúrate de que este campo esté aquí
        'status',
        // 'image_path', // Si lo agregas en la migración
    ];

    /**
     * Set the final price when base price or discount changes.
     * This is an accessor, not a mutator, better to handle calculation in controller or an observer.
     * For a simple setup, we'll calculate it on save in the controller.
     * Or you can define an accessor if you only want to retrieve it calculated:
     */
    // public function getFinalPriceAttribute($value)
    // {
    //     // If you store final_price in DB, just return $value
    //     return $this->base_price * (1 - ($this->discount_percentage / 100));
    // }

    // If you add a relationship to Ally
    // public function ally()
    // {
    //     return $this->belongsTo(Ally::class); // Assuming Ally model exists
    // }
}
