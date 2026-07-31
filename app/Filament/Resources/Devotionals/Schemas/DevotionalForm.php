<?php

namespace App\Filament\Resources\Devotionals\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class DevotionalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('month')
                    ->options([
                        1 => 'Janeiro',
                        2 => 'Fevereiro',
                        3 => 'Março',
                        4 => 'Abril',
                        5 => 'Maio',
                        6 => 'Junho',
                        7 => 'Julho',
                        8 => 'Agosto',
                        9 => 'Setembro',
                        10 => 'Outubro',
                        11 => 'Novembro',
                        12 => 'Dezembro',
                    ])
                    ->required(),
                TextInput::make('day')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31),
                Grid::make(1)
                    ->schema([
                        TextInput::make('reference_old_testament')
                            ->label('Referência (Antigo Testamento)')
                            ->required(),
                        RichEditor::make('content_old_testament')
                            ->label('Conteúdo (Antigo Testamento)')
                            ->required(),

                    ]),
                Grid::make(1)
                    ->schema([
                        TextInput::make('reference_new_testament')
                            ->label('Referência (Novo Testamento)')
                            ->required(),
                        RichEditor::make('content_new_testament')
                            ->label('Conteúdo (Novo Testamento)')
                            ->required(),
                    ]),
            ]);
    }
}
