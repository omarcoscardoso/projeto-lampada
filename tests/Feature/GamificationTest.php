<?php

use App\Models\Devotional;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access gamification endpoints', function () {
    getJson('/api/user/gamification')->assertStatus(401);
    postJson('/api/user/gamification/complete', ['date' => '2026-08-05'])->assertStatus(401);
});

test('authenticated user can fetch gamification data', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->getJson('/api/user/gamification')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'annual_read_count',
                'annual_total_days',
                'annual_percentage',
                'current_streak',
                'weekly_days',
                'completed_dates',
            ],
        ]);
});

test('authenticated user can mark reading as complete and update streak', function () {
    $user = User::factory()->create();

    $today = Carbon::now();
    $devotional = Devotional::create([
        'month' => $today->month,
        'day' => $today->day,
        'reference_old_testament' => 'Gênesis 1:1',
        'content_old_testament' => '<p>No princípio</p>',
        'reference_new_testament' => 'Mateus 1:1',
        'content_new_testament' => '<p>Livro da geração</p>',
    ]);

    actingAs($user)
        ->postJson('/api/user/gamification/complete', [
            'date' => $today->format('Y-m-d'),
        ])
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Leitura concluída com sucesso!',
        ])
        ->assertJsonPath('data.annual_read_count', 1)
        ->assertJsonPath('data.current_streak', 1);

    expect(UserProgress::where('user_id', $user->id)->where('devotional_id', $devotional->id)->exists())->toBeTrue();
});

test('breaking weekly streak does not erase annual completed dates', function () {
    $user = User::factory()->create();

    // Devocional de 10 dias atrás
    $pastDate = Carbon::now()->subDays(10);
    $pastDevotional = Devotional::create([
        'month' => $pastDate->month,
        'day' => $pastDate->day,
        'reference_old_testament' => 'Salmos 23:1',
        'content_old_testament' => '<p>O Senhor é meu pastor</p>',
        'reference_new_testament' => 'João 3:16',
        'content_new_testament' => '<p>Porque Deus amou o mundo</p>',
    ]);

    // Registrar leitura antiga
    UserProgress::create([
        'user_id' => $user->id,
        'devotional_id' => $pastDevotional->id,
        'completed_at' => $pastDate,
    ]);

    // Buscar dados atuais de gamificação
    $response = actingAs($user)
        ->getJson('/api/user/gamification')
        ->assertStatus(200);

    // Como o usuário não leu ontem nem hoje, a ofensiva (streak) deve ser 0
    expect($response->json('data.current_streak'))->toBe(0);

    // Mas a leitura antiga deve continuar contada no plano anual (annual_read_count >= 1) e listada no calendário
    expect($response->json('data.annual_read_count'))->toBeGreaterThanOrEqual(1);
    expect($response->json('data.completed_dates'))->toContain($pastDate->format('Y-m-d'));
});

test('marking reading complete persists even if devotional row does not exist prior to reading', function () {
    $user = User::factory()->create();
    $targetDate = '2026-11-15';

    actingAs($user)
        ->postJson('/api/user/gamification/complete', [
            'date' => $targetDate,
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.annual_read_count', 1);

    // Ao recarregar ou consultar via GET, a informação deve ser mantida
    actingAs($user)
        ->getJson('/api/user/gamification')
        ->assertStatus(200)
        ->assertJsonPath('data.annual_read_count', 1)
        ->assertJsonPath('data.completed_dates', [$targetDate]);
});
