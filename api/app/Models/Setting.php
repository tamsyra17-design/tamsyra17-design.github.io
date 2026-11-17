<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The group whose settings never leave the admin panel.
     */
    public const PRIVATE_GROUP = 'private';

    /**
     * The groups an admin can file a setting under.
     *
     * @var list<string>
     */
    public const GROUPS = [
        'general',
        'contact',
        'social',
        'shipping',
        'payments',
        self::PRIVATE_GROUP,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'group' => 'general',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    /**
     * Settings that are safe to publish to the storefront.
     *
     * @param  Builder<Setting>  $query
     */
    public function scopePublic(Builder $query): void
    {
        $query->where('group', '!=', self::PRIVATE_GROUP);
    }

    /**
     * A single setting's value, or the default when it is not set.
     */
    public static function valueOf(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        return $setting === null ? $default : $setting->value;
    }
}
