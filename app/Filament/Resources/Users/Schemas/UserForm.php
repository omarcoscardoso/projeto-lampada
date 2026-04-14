<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('roles')
                    ->label('Papéis')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    // ->visible(fn(): bool => \Illuminate\Support\Facades\Auth::user()?->hasRole('super_admin') ?? false)
                    // ->disabled(fn(): bool => !(\Illuminate\Support\Facades\Auth::user()?->hasRole('super_admin') ?? false))
                    ->searchable(),
            ]);
    }
}
