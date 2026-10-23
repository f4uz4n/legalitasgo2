<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    protected $fillable = [
        'key',
        'value',
        'group',
        'keterangan',
    ];

    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value, $group = 'umum', $keterangan = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'keterangan' => $keterangan]
        );
    }
}
