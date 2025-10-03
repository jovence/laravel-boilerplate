<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuidAndFilters
{
    protected static function bootHasUuidAndFilters()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public static function findOrFailUuid(string $uuid)
    {
        return static::where('id', $uuid)->firstOrFail();
    }

    public function scopeFilter($query, array $filters = [])
    {
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $query->where($field, $value);
            }
        }
        return $query;
    }
}
