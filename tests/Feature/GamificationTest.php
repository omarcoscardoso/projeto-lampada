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
                'best_streak',
                'total_completed',
                'weekly_days',
                'monthly_stats',
                'completed_dates',
            ],
        ]);
});

test('weekly days align correctly starting from Monday with correct is_today marker', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->getJson('/api/user/gamification')->assertStatus(200);

    $weeklyDays = $response->json('data.weekly_days');
    expect($weeklyDays)->toHaveCount(7);
    expect($weeklyDays[0]['day'])->toBe('Seg');
    expect($weeklyDays[1]['day'])->toBe('Ter');
    expect($weeklyDays[2]['day'])->toBe('Qua');
    expect($weeklyDays[3]['day'])->toBe('Qui');
    expect($weeklyDays[4]['day'])->toBe('Sex');
    expect($weeklyDays[5]['day'])->toBe('Sáb');
    expect($weeklyDays[6]['day'])->toBe('Dom');

    $todayFormatted = Carbon::now(config('app.timezone', 'America/Sao_Paulo'))->format('Y-m-d');
    $todayDayObj = collect($weeklyDays)->firstWhere('is_today', true);
    expect($todayDayObj)->not->toBeNull();
    expect($todayDayObj['date'])->toBe($todayFormatted);
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
    $res = actingAs($user)
        ->getJson('/api/user/gamification')
        ->assertStatus(200)
        ->assertJsonPath('data.annual_read_count', 1);

    expect($res->json('data.completed_dates'))->toContain($targetDate);
});

test('reading yesterday devotional marks only yesterday date and not today or tomorrow date', function () {
    $user = User::factory()->create();
    $yesterday = Carbon::now()->subDay()->format('Y-m-d');
    $today = Carbon::now()->format('Y-m-d');

    actingAs($user)
        ->postJson('/api/user/gamification/complete', [
            'date' => $yesterday,
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.annual_read_count', 1);

    $res = actingAs($user)
        ->getJson('/api/user/gamification')
        ->assertStatus(200);

    expect($res->json('data.completed_dates'))->toContain($yesterday);
    expect($res->json('data.completed_dates'))->not->toContain($today);
});

test('annual reading count accumulates 365 readings regardless of calendar year and cycles at 365', function () {
    $user = User::factory()->create();

    for ($month = 1; $month <= 12; $month++) {
        for ($day = 1; $day <= 31; $day++) {
            if (UserProgress::where('user_id', $user->id)->count() >= 365) {
                break 2;
            }
            $devotional = Devotional::create([
                'month' => $month,
                'day' => $day,
                'reference_old_testament' => 'Ref AT',
                'content_old_testament' => 'Txt AT',
                'reference_new_testament' => 'Ref NT',
                'content_new_testament' => 'Txt NT',
            ]);
            UserProgress::create([
                'user_id' => $user->id,
                'devotional_id' => $devotional->id,
                'completed_at' => Carbon::now()->subDays(10),
            ]);
        }
    }

    $response = actingAs($user)->getJson('/api/user/gamification')->assertStatus(200);

    expect($response->json('data.annual_read_count'))->toBe(365);
    expect($response->json('data.annual_percentage'))->toEqual(100);

    // Adicionar a 366ª leitura (novo ciclo)
    $extraDevotional = Devotional::create([
        'month' => 12,
        'day' => 31,
        'reference_old_testament' => 'Ref AT Extra',
        'content_old_testament' => 'Txt AT Extra',
        'reference_new_testament' => 'Ref NT Extra',
        'content_new_testament' => 'Txt NT Extra',
    ]);
    UserProgress::create([
        'user_id' => $user->id,
        'devotional_id' => $extraDevotional->id,
        'completed_at' => Carbon::now(),
    ]);

    $response2 = actingAs($user)->getJson('/api/user/gamification')->assertStatus(200);

    expect($response2->json('data.annual_read_count'))->toBe(1);
    expect($response2->json('data.annual_percentage'))->toEqual(0.3);
});
