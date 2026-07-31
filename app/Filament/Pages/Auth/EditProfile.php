<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informações do Perfil')
                    ->description('Gerencie seus dados e preferências de conta.')
                    ->schema([
                        TextEntry::make('avatar_preview')
                            ->label('')
                            ->state(function (): HtmlString {
                                $avatar = Auth::user()?->avatar;
                                $src = $avatar ?: 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()?->name ?? 'U').'&size=96&background=e5e7eb&color=374151';

                                return new HtmlString(
                                    '<div style="display:flex;align-items:center;gap:20px;padding:8px 0;">
                                        <img src="'.e($src).'" alt="Avatar"
                                            style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid #f59e0b;">
                                        <div>
                                            <p style="font-weight:600;font-size:0.95rem;">Foto de Perfil</p>
                                            <p style="color:#6b7280;font-size:0.8rem;">Sua foto é gerenciada pela conta Google.</p>
                                        </div>
                                    </div>'
                                );
                            }),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome Completo')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('E-mail de Login')
                                    ->email()
                                    ->required()
                                    ->disabled(fn () => ! $this->isAdmin())
                                    ->helperText(fn () => $this->isAdmin()
                                        ? null
                                        : 'E-mail não pode ser alterado.'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->label('Nova Senha')
                                    ->password()
                                    ->autocomplete('new-password')
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->placeholder('Deixe em branco para manter a atual'),

                                TextInput::make('passwordConfirmation')
                                    ->label('Confirmar Nova Senha')
                                    ->password()
                                    ->same('password')
                                    ->dehydrated(false)
                                    ->placeholder('Repita a nova senha'),
                            ]),
                    ]),
            ]);
    }

    private function isAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasRole('super_admin') ?? false;
    }

    public function getMaxWidth(): Width|string|null
    {
        return Width::TwoExtraLarge;
    }

    protected function getRedirectUrl(): string
    {
        return '/admin';
    }
}
