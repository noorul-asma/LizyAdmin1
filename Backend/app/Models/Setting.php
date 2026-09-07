<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Grouped key-value application settings (e.g. "general.app_name",
 * "modules.product_enabled"). Every value is JSON-encoded in storage so a
 * single string column can hold strings, booleans, or numbers uniformly -
 * callers never need to know or care which.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();

        return $row ? json_decode($row->value, true) : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => json_encode($value)]);
    }

    /** Fetch several keys at once, each falling back to $defaults[$key] when unset. */
    public static function many(array $keys, array $defaults = []): array
    {
        $rows = static::whereIn('key', $keys)->pluck('value', 'key');

        return collect($keys)->mapWithKeys(function (string $key) use ($rows, $defaults) {
            $value = $rows->has($key) ? json_decode($rows[$key], true) : ($defaults[$key] ?? null);

            return [$key => $value];
        })->all();
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::set($key, $value);
        }
    }
}
