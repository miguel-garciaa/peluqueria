<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait DeletesPublicImage
{
    protected static function bootDeletesPublicImage(): void
    {
        static::updated(function (Model $model): void {
            if (! $model->wasChanged('image_path')) {
                return;
            }

            static::deletePublicImageAfterCommit($model->getRawOriginal('image_path'));
        });

        static::deleted(function (Model $model): void {
            static::deletePublicImageAfterCommit($model->getAttribute('image_path'));
        });
    }

    private static function deletePublicImageAfterCommit(mixed $path): void
    {
        if (! is_string($path) || blank($path)) {
            return;
        }

        DB::afterCommit(
            fn () => Storage::disk('public')->delete($path),
        );
    }
}
