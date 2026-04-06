<?php

namespace App\Http\Controllers;

use App\Services\BibleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BibleController extends Controller
{
    /**
     * Get biblical text for devotions.
     */
    public function __invoke(Request $request, BibleService $bibleService): JsonResponse
    {
        $request->validate([
            'ref_old' => 'nullable|string',
            'ref_new' => 'nullable|string',
        ]);

        $refOld = $request->query('ref_old');
        $refNew = $request->query('ref_new');

        $result = [];

        if ($refOld) {
            $result['old_testament'] = $bibleService->getVersesByReference($refOld);
        }

        if ($refNew) {
            $result['new_testament'] = $bibleService->getVersesByReference($refNew);
        }

        return response()->json($result);
    }
}
