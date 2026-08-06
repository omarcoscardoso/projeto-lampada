<?php

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('guest user can view landing page', function () {
    get('/')
        ->assertStatus(200)
        ->assertSee('Entrar');
});

test('authenticated user accessing root URL is redirected to app', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/')
        ->assertRedirect(route('app'));
});

test('authenticated user accessing landing page via sobre link can view landing page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/?sobre=1')
        ->assertStatus(200)
        ->assertSee('Ir para o App');
});

test('user can log out via logout route', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/logout')
        ->assertRedirect('/');

    assertGuest();
});

test('regular user with panel_user role can log in and is redirected to app', function () {
    $role = Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'email' => 'teste.regular@example.com',
        'password' => 'password123',
    ]);
    $user->assignRole($role);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'teste.regular@example.com',
            'password' => 'password123',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect(route('app'));

    $this->assertAuthenticatedAs($user);
});

test('regular user does not see admin panel link on app page', function () {
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);
    $user->assignRole($role);

    actingAs($user)
        ->get('/app')
        ->assertStatus(200)
        ->assertDontSee('Painel Admin');
});

test('admin user sees admin panel link on app page', function () {
    $user = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $user->assignRole($adminRole);

    actingAs($user)
        ->get('/app')
        ->assertStatus(200)
        ->assertSee('Painel Admin');
});
