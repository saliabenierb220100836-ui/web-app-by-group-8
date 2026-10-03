<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /** Defaults used until the admin saves a value on the Pricing page. */
    public const DEFAULTS = [
        'account_fee' => '60',
        'rate_standard' => '25',
        'rate_vip' => '40',
        'vip_min_advance_minutes' => '60',
        'booking_max_hours' => '8',
        'billing_increment_minutes' => '15',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::query()->find($key);

        if ($row && $row->value !== null) {
            return $row->value;
        }

        return $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function number(string $key): float
    {
        return (float) static::get($key);
    }

    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}
