<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Producto')->schema([
                    ImageEntry::make('image_path')
                        ->label('Fotografía')
                        ->disk('public')
                        ->height(280)
                        ->columnSpanFull(),
                    TextEntry::make('name')->label('Nombre'),
                    TextEntry::make('sku')->label('Referencia / SKU')->placeholder('Sin referencia'),
                    TextEntry::make('category')
                        ->label('Categoría')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => Product::categoryOptions()[$state] ?? 'Otros'),
                    TextEntry::make('is_active')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn (bool $state): string => $state ? 'En uso' : 'Fuera de uso')
                        ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    TextEntry::make('description')
                        ->label('Descripción')
                        ->placeholder('Sin descripción')
                        ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
                Section::make('Precio y existencias')->schema([
                    TextEntry::make('price')->label('Precio unitario')->money('EUR', locale: 'es'),
                    TextEntry::make('units')->label('Unidades')->suffix(' uds.'),
                    TextEntry::make('low_stock_threshold')->label('Aviso de reposición')->suffix(' uds. o menos'),
                    TextEntry::make('updated_at')->label('Última actualización')->dateTime('d/m/Y H:i'),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
