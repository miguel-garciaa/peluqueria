<?php

namespace App\Filament\Resources\Professionals;

use App\Filament\Resources\Professionals\Pages\CreateProfessional;
use App\Filament\Resources\Professionals\Pages\EditProfessional;
use App\Filament\Resources\Professionals\Pages\ListProfessionals;
use App\Filament\Resources\Professionals\Pages\ViewProfessional;
use App\Filament\Resources\Professionals\Schemas\ProfessionalForm;
use App\Filament\Resources\Professionals\Schemas\ProfessionalInfolist;
use App\Filament\Resources\Professionals\Tables\ProfessionalsTable;
use App\Models\Professional;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProfessionalResource extends Resource
{
    protected static ?string $model = Professional::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Profesionales';

    protected static ?string $modelLabel = 'profesional';

    protected static ?string $pluralModelLabel = 'profesionales';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProfessionalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProfessionalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfessionalsTable::configure($table);
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
            'index' => ListProfessionals::route('/'),
            'create' => CreateProfessional::route('/create'),
            'view' => ViewProfessional::route('/{record}'),
            'edit' => EditProfessional::route('/{record}/edit'),
        ];
    }
}
