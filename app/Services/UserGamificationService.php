<?php

namespace App\Services;

use App\Models\Devotional;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Carbon;

class UserGamificationService
{
    /**
     * Retorna os dados completos de gamificação do usuário.
     */
    public function getUserGamificationData(User $user, ?string $referenceDate = null): array
    {
        $now = Carbon::now();
        $year = $referenceDate ? Carbon::parse($referenceDate)->year : $now->year;

        $progresses = UserProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with('devotional')
            ->get();

        $completedDatesSet = [];

        foreach ($progresses as $progress) {
            if ($progress->completed_at) {
                $completedDatesSet[$progress->completed_at->format('Y-m-d')] = true;
            }

            if ($progress->devotional) {
                $dateStr = sprintf(
                    '%04d-%02d-%02d',
                    $year,
                    (int) $progress->devotional->month,
                    (int) $progress->devotional->day
                );
                $completedDatesSet[$dateStr] = true;
            }
        }

        $completedDates = array_keys($completedDatesSet);

        // Leitura Anual (meta de 365 leituras acumuladas, independente do ano civil)
        $totalCompleted = $progresses->pluck('devotional_id')->unique()->count();
        $totalGoal = 365;

        if ($totalCompleted === 0) {
            $annualReadCount = 0;
            $annualPercentage = 0.0;
        } else {
            $remainder = $totalCompleted % $totalGoal;
            $annualReadCount = ($remainder === 0) ? $totalGoal : $remainder;
            $annualPercentage = round(($annualReadCount / $totalGoal) * 100, 1);
        }

        // Ofensiva Semanal (Streak)
        $currentStreak = $this->calculateStreak($completedDatesSet, $now);

        // Dias da semana corrente (Segunda a Domingo)
        $startOfWeek = $now->copy()->startOfWeek();
        $weeklyDays = [];
        $dayLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

        for ($i = 0; $i < 7; $i++) {
            $dateObj = $startOfWeek->copy()->addDays($i);
            $dateStr = $dateObj->format('Y-m-d');
            $weeklyDays[] = [
                'day' => $dayLabels[$i],
                'date' => $dateStr,
                'completed' => isset($completedDatesSet[$dateStr]),
                'is_today' => $dateStr === $now->format('Y-m-d'),
            ];
        }

        return [
            'annual_read_count' => $annualReadCount,
            'annual_total_days' => $totalGoal,
            'annual_percentage' => $annualPercentage,
            'current_streak' => $currentStreak,
            'weekly_days' => $weeklyDays,
            'completed_dates' => $completedDates,
        ];
    }

    /**
     * Marca a leitura da data especificada como concluída para o usuário.
     */
    public function markAsCompleted(User $user, string $dateString): array
    {
        $date = Carbon::parse($dateString);

        $devotional = Devotional::firstOrCreate(
            [
                'month' => $date->month,
                'day' => $date->day,
            ],
            [
                'reference_old_testament' => '',
                'content_old_testament' => '',
                'reference_new_testament' => '',
                'content_new_testament' => '',
            ]
        );

        UserProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'devotional_id' => $devotional->id,
            ],
            [
                'completed_at' => Carbon::now(),
            ]
        );

        return $this->getUserGamificationData($user, $dateString);
    }

    /**
     * Calcula a ofensiva atual (dias consecutivos de leitura).
     */
    private function calculateStreak(array $completedDatesSet, Carbon $today): int
    {
        $todayStr = $today->format('Y-m-d');
        $yesterdayStr = $today->copy()->subDay()->format('Y-m-d');

        $checkDate = null;
        if (isset($completedDatesSet[$todayStr])) {
            $checkDate = $today->copy();
        } elseif (isset($completedDatesSet[$yesterdayStr])) {
            $checkDate = $today->copy()->subDay();
        } else {
            return 0;
        }

        $streak = 0;
        while (isset($completedDatesSet[$checkDate->format('Y-m-d')])) {
            $streak++;
            $checkDate->subDay();
        }

        return $streak;
    }
}
