<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Auto-generates a unique `slug` from slugSourceField() on save when left
 * blank, appending -2, -3, ... on collision. Shared by NewsPost and
 * MoneyPage rather than duplicated per model.
 */
trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function (self $model) {
            if (blank($model->slug)) {
                $model->slug = static::uniqueSlugFor($model->{static::slugSourceField()}, $model->id);
            }
        });
    }

    public static function uniqueSlugFor(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /** Field to slugify when `slug` is left blank. Override per model. */
    protected static function slugSourceField(): string
    {
        return 'title';
    }
}
