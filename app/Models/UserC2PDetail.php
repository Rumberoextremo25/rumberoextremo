<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt; // Para encriptar/desencriptar

class UserC2PDetail extends Model
{
    use HasFactory;

    protected $table = 'user_c2p_details'; // Nombre de la tabla
    protected $fillable = [
        'user_id',
        'encrypted_phone_number',
        'encrypted_id_card',
        'bank_code',
        'account_type',
    ];

    // Relación con el modelo User (opcional pero recomendado)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mutador para encriptar el número de teléfono antes de guardar
    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['encrypted_phone_number'] = Crypt::encryptString($value);
    }

    // Accesor para desencriptar el número de teléfono al recuperarlo
    public function getPhoneNumberAttribute()
    {
        try {
            return Crypt::decryptString($this->attributes['encrypted_phone_number']);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Manejar error de desencriptación (ej. clave cambiada o datos corruptos)
            return null;
        }
    }

    // Mutador para encriptar la cédula antes de guardar
    public function setIdCardAttribute($value)
    {
        $this->attributes['encrypted_id_card'] = Crypt::encryptString($value);
    }

    // Accesor para desencriptar la cédula al recuperarlo
    public function getIdCardAttribute()
    {
        try {
            return Crypt::decryptString($this->attributes['encrypted_id_card']);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Manejar error de desencriptación
            return null;
        }
    }
}