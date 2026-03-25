<?php

namespace App\Http\Controllers;

use App\Models\Devotional;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class DevotionalController extends Controller
{
    public function __invoke(string $date): JsonResponse
    {
        $parsedDate = Date::parse($date);

        $devotional = Devotional::query()
            ->where('month', $parsedDate->month)
            ->where('day', $parsedDate->day)
            ->first();

        if ($devotional) {
            return response()->json([
                'reference_old_testament' => $devotional->reference_old_testament,
                'content_old_testament' => $devotional->content_old_testament,
                'reference_new_testament' => $devotional->reference_new_testament,
                'content_new_testament' => $devotional->content_new_testament,
            ]);
        }

        return response()->json(['message' => 'devocional não encontrado'], 404);
    }
}
