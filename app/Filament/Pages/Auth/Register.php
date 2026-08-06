<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Register extends BaseRegister
{
    protected function handleRegistration(array $data): Model
    {
        /** @var User $user */
        $user = parent::handleRegistration($data);

        $role = Role::firstOrCreate([
            'name' => 'panel_user',
            'guard_name' => 'web',
        ]);

        $user->assignRole($role);

        return $user;
    }
}
