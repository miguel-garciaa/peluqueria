<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('15s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('email')->label('Correo')->searchable()->copyable(),
                TextColumn::make('phone')->label('Teléfono')->searchable()->placeholder('—'),
                TextColumn::make('appointments_count')->counts('appointments')->label('Reservas')->sortable(),
                TextColumn::make('created_at')->label('Alta')->dateTime('d/m/Y', timezone: config('app.business_timezone'))->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
