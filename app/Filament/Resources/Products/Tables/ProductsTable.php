<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->searchPlaceholder('Buscar productos')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->height(52)
                    ->visibleFrom('md'),
                TextColumn::make('name')
                    ->label('Producto')
                    ->description(fn (Product $record): ?string => $record->sku)
                    ->searchable(['name', 'sku', 'description'])
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Product::categoryOptions()[$state] ?? 'Otros')
                    ->color(fn (string $state): string => match ($state) {
                        'machinery', 'tools' => 'info',
                        'hair_care', 'styling' => 'primary',
                        'color' => 'warning',
                        'furniture' => 'gray',
                        'disposables' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('EUR', locale: 'es')
                    ->sortable()
                    ->visibleFrom('sm'),
                TextColumn::make('units')
                    ->label('Stock')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => $state.' uds.')
                    ->color(fn (int $state, Product $record): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= $record->low_stock_threshold => 'warning',
                        default => 'success',
                    })
                    ->description(fn (Product $record): ?string => $record->units <= $record->low_stock_threshold
                        ? 'Reponer'
                        : null)
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'En uso' : 'Fuera de uso')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->dateTimeTooltip('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(Product::categoryOptions())
                    ->multiple()
                    ->native(false),
                Filter::make('low_stock')
                    ->label('Necesita reposición')
                    ->query(fn (Builder $query): Builder => $query->lowStock()),
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('En uso')
                    ->falseLabel('Fuera de uso')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->iconButton()
                    ->tooltip('Ver producto'),
                EditAction::make()
                    ->label('Editar')
                    ->iconButton()
                    ->tooltip('Editar producto'),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->iconButton()
                    ->tooltip('Eliminar producto')
                    ->modalHeading('Eliminar producto')
                    ->modalDescription('Se eliminarán la ficha y su fotografía. Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar producto'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar productos seleccionados')
                        ->modalDescription('Se eliminarán las fichas y sus fotografías. Esta acción no se puede deshacer.'),
                ]),
            ])
            ->emptyStateHeading('El inventario está vacío')
            ->emptyStateDescription('Añade el primer producto, herramienta o máquina para empezar a controlar sus existencias.')
            ->emptyStateIcon('heroicon-o-archive-box')
            ->emptyStateActions([
                CreateAction::make()->label('Añadir primer producto'),
            ]);
    }
}
