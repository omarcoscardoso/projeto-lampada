<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access profile endpoints', function () {
    getJson('/api/user/profile')->assertStatus(401);
    putJson('/api/user/profile', ['name' => 'Novo Nome'])->assertStatus(401);
    putJson('/api/user/password', ['password' => 'newpassword123'])->assertStatus(401);
});

test('authenticated user can fetch profile data with gamification stats', function () {
    $user = User::factory()->create([
        'name' => 'Marcos Cardoso',
        'email' => 'marcos@example.com',
    ]);

    actingAs($user)
        ->getJson('/api/user/profile')
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'user' => [
                'name' => 'Marcos Cardoso',
                'email' => 'marcos@example.com',
            ],
        ])
        ->assertJsonStructure([
            'success',
            'user' => ['id', 'name', 'email', 'avatar', 'is_google', 'has_password', 'created_at_formatted'],
            'stats' => ['annual_read_count', 'current_streak', 'best_streak', 'total_completed', 'monthly_stats'],
        ]);
});

test('authenticated user can update profile name', function () {
    $user = User::factory()->create([
        'name' => 'Nome Antigo',
    ]);

    actingAs($user)
        ->putJson('/api/user/profile', [
            'name' => 'Nome Atualizado',
        ])
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso!',
            'user' => [
                'name' => 'Nome Atualizado',
            ],
        ]);

    expect($user->fresh()->name)->toBe('Nome Atualizado');
});

test('authenticated user can change password when providing correct current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);

    actingAs($user)
        ->putJson('/api/user/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ])
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Senha alterada com sucesso!',
        ]);

    expect(Hash::check('newpassword456', $user->fresh()->password))->toBeTrue();
});

test('authenticated user fails to change password with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);

    actingAs($user)
        ->putJson('/api/user/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'A senha atual informada está incorreta.',
        ]);

    expect(Hash::check('oldpassword123', $user->fresh()->password))->toBeTrue();
});

test('password change requires matching confirmation and minimum length', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);

    actingAs($user)
        ->putJson('/api/user/password', [
            'current_password' => 'oldpassword123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    actingAs($user)
        ->putJson('/api/user/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword456',
            'password_confirmation' => 'different456',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
