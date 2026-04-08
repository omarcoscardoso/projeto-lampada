<?php

namespace App\Filament\Resources\Devotionals;

use App\Filament\Resources\Devotionals\Pages\CreateDevotional;
use App\Filament\Resources\Devotionals\Pages\EditDevotional;
use App\Filament\Resources\Devotionals\Pages\ListDevotionals;
use App\Filament\Resources\Devotionals\Schemas\DevotionalForm;
use App\Filament\Resources\Devotionals\Tables\DevotionalsTable;
use App\Models\Devotional;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DevotionalResource extends Resource
{
    protected static ?string $model = Devotional::class;

    protected static ?string $navigationLabel = 'Leituras';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DevotionalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DevotionalsTable::configure($table);
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
            'index' => ListDevotionals::route('/'),
            'create' => CreateDevotional::route('/create'),
            'edit' => EditDevotional::route('/{record}/edit'),
        ];
    }
}
