<?php

namespace App\Filament\Resources\ProfessionalCalendarEntries;

use App\Filament\Resources\ProfessionalCalendarEntries\Pages\CreateProfessionalCalendarEntry;
use App\Filament\Resources\ProfessionalCalendarEntries\Pages\EditProfessionalCalendarEntry;
use App\Filament\Resources\ProfessionalCalendarEntries\Pages\ListProfessionalCalendarEntries;
use App\Filament\Resources\ProfessionalCalendarEntries\Pages\ViewProfessionalCalendarEntry;
use App\Filament\Resources\ProfessionalCalendarEntries\Schemas\ProfessionalCalendarEntryForm;
use App\Filament\Resources\ProfessionalCalendarEntries\Schemas\ProfessionalCalendarEntryInfolist;
use App\Filament\Resources\ProfessionalCalendarEntries\Tables\ProfessionalCalendarEntriesTable;
use App\Models\ProfessionalCalendarEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProfessionalCalendarEntryResource extends Resource
{
    protected static ?string $model = ProfessionalCalendarEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static ?string $navigationLabel = 'Bloqueos y excepciones';

    protected static ?string $modelLabel = 'excepción de calendario';

    protected static ?string $pluralModelLabel = 'bloqueos y excepciones';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return ProfessionalCalendarEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProfessionalCalendarEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfessionalCalendarEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfessionalCalendarEntries::route('/'),
            'create' => CreateProfessionalCalendarEntry::route('/create'),
            'view' => ViewProfessionalCalendarEntry::route('/{record}'),
            'edit' => EditProfessionalCalendarEntry::route('/{record}/edit'),
        ];
    }
}
