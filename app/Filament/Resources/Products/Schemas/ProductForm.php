<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Producto')
                    ->description('Datos para identificar el artículo dentro del inventario.')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Fotografía del producto')
                            ->disk('public')
                            ->directory('inventory/products')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->imageEditor()
                            ->imageEditorAspectRatioOptions(['1:1', '4:3', '16:9'])
                            ->imagePreviewHeight('260')
                            ->openable()
                            ->helperText('JPG, PNG o WebP. Máximo 5 MB. Puedes recortar la imagen antes de guardarla.')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->placeholder('Ej. Champú reparador 500 ml')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sku')
                            ->label('Referencia / SKU')
                            ->placeholder('Ej. CH-REP-500')
                            ->unique(ignoreRecord: true)
                            ->maxLength(80)
                            ->helperText('Opcional. Úsala para localizar el producto rápidamente.'),
                        Select::make('category')
                            ->label('Categoría')
                            ->options(Product::categoryOptions())
                            ->native(false)
                            ->searchable()
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Producto en uso')
                            ->helperText('Desactívalo para ocultarlo de la vista habitual sin perder su historial.')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Descripción breve')
                            ->placeholder('Marca, tamaño, uso principal o cualquier detalle útil.')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Precio y existencias')
                    ->description('Controla el valor y el nivel disponible de cada artículo.')
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio unitario')
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->maxValue(99999999.99)
                            ->step(0.01)
                            ->required(),
                        TextInput::make('units')
                            ->label('Unidades disponibles')
                            ->integer()
                            ->suffix('uds.')
                            ->minValue(0)
                            ->maxValue(99999999)
                            ->default(0)
                            ->required(),
                        TextInput::make('low_stock_threshold')
                            ->label('Avisar cuando queden')
                            ->integer()
                            ->suffix('uds. o menos')
                            ->minValue(0)
                            ->maxValue(99999999)
                            ->default(3)
                            ->required()
                            ->helperText('El producto aparecerá marcado para reposición al alcanzar esta cantidad.'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
