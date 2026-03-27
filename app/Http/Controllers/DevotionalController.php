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
            $oldRef = $devotional->reference_old_testament;
            $oldContext = \App\Services\WhatsAppFormatter::format($devotional->content_old_testament);
            $newRef = $devotional->reference_new_testament;
            $newContext = \App\Services\WhatsAppFormatter::format($devotional->content_new_testament);

            $whatsappMessage = "*{$oldRef}*\n{$oldContext}\n\n*{$newRef}*\n{$newContext}";

            return response()->json([
                'reference_old_testament' => $oldRef,
                'content_old_testament' => $devotional->content_old_testament,
                'reference_new_testament' => $newRef,
                'content_new_testament' => $devotional->content_new_testament,
                'whatsapp_message' => $whatsappMessage,
            ]);
        }

        return response()->json(['message' => 'devocional não encontrado'], 404);
    }
}
