<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
