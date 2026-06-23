<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'type'];

    /**
     * Default values bawaan jika belum ada di database.
     */
    const DEFAULTS = [
        'admin_fee' => '2000.00',
    ];

    /**
     * Ambil value setting berdasarkan key, dengan default fallback.
     */
    public static function getValue(string $key, ?string $default = null): string
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            return $setting->value;
        }

        return $default ?? (self::DEFAULTS[$key] ?? '');
    }
}
