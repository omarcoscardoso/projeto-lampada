<?php

namespace App\Http\Controllers;

use App\Models\Devotional;
use App\Services\WhatsAppFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class DevotionalController extends Controller
{
    public function __invoke(string $date): JsonResponse
    {
        $parsedDate = Date::parse($date);
        $cacheKey = "devotional_{$parsedDate->format('m-d')}";

        $data = Cache::remember($cacheKey, now()->addDay(), function () use ($parsedDate) {
            $devotional = Devotional::query()
                ->where('month', $parsedDate->month)
                ->where('day', $parsedDate->day)
                ->first();

            if (! $devotional) {
                return null;
            }

            $oldRef = $devotional->reference_old_testament;
            $oldContext = WhatsAppFormatter::format($devotional->content_old_testament);
            $newRef = $devotional->reference_new_testament;
            $newContext = WhatsAppFormatter::format($devotional->content_new_testament);

            return [
                'reference_old_testament' => $oldRef,
                'content_old_testament' => $devotional->content_old_testament,
                'reference_new_testament' => $newRef,
                'content_new_testament' => $devotional->content_new_testament,
                'whatsapp_message' => "*{$oldRef}*\n{$oldContext}\n\n*{$newRef}*\n{$newContext}",
            ];
        });

        if ($data) {
            return response()->json($data);
        }

        return response()->json(['message' => 'devocional não encontrado'], 404);
    }
}
