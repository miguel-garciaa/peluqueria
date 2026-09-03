<?php

namespace App\Models;

use App\Models\Concerns\DeletesPublicImage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'sku',
    'category',
    'description',
    'image_path',
    'price',
    'units',
    'low_stock_threshold',
    'is_active',
])]
class Product extends Model
{
    use DeletesPublicImage;

    /** @return array<string, string> */
    public static function categoryOptions(): array
    {
        return [
            'hair_care' => 'Cuidado capilar',
            'color' => 'Coloración',
            'styling' => 'Acabado y styling',
            'tools' => 'Herramientas',
            'machinery' => 'Maquinaria',
            'furniture' => 'Mobiliario',
            'disposables' => 'Desechables',
            'other' => 'Otros',
        ];
    }

    public function categoryLabel(): string
    {
        return self::categoryOptions()[$this->category] ?? 'Otros';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('units', '<=', 'low_stock_threshold');
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'units' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
