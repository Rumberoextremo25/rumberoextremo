<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Ajustes clave/valor de la aplicación (editables desde Configuración).
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'app.settings';

    /**
     * Valores por defecto si todavía no se han guardado.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        'support_email' => 'soporteitsolutech@gmail.com',
        'support_whatsapp' => '584124992125',
        'sales_email' => 'soporteitsolutech@gmail.com',
        'chat_ai_enabled' => '1',
    ];

    /**
     * @return array<string, string>
     */
    public static function all_cached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                return self::query()->pluck('value', 'key')->all();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $values = self::all_cached();

        if (array_key_exists($key, $values) && filled($values[$key])) {
            return (string) $values[$key];
        }

        return $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function put(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
