<?php

use App\Filament\Pages\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);
});

test('registration page can be rendered', function () {
    get('/admin/register')->assertStatus(200);
});

test('new user can register with name, email and password', function () {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Novo Usuario Sem Google',
            'email' => 'semgoogle@example.com',
            'password' => 'password123',
            'passwordConfirmation' => 'password123',
        ])
        ->call('register')
        ->assertHasNoFormErrors()
        ->assertRedirect(route('app'));

    assertDatabaseHas(User::class, [
        'email' => 'semgoogle@example.com',
        'name' => 'Novo Usuario Sem Google',
    ]);

    $user = User::where('email', 'semgoogle@example.com')->first();
    expect($user)->not->toBeNull();
    expect(Hash::check('password123', $user->password))->toBeTrue();
    expect($user->fresh()->hasRole('panel_user'))->toBeTrue();
});

test('registration fails when email is already taken', function () {
    User::factory()->create(['email' => 'existente@example.com']);

    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Outro Usuario',
            'email' => 'existente@example.com',
            'password' => 'password123',
            'passwordConfirmation' => 'password123',
        ])
        ->call('register')
        ->assertHasFormErrors(['email']);
});
