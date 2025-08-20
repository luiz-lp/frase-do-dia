<?php

use Illuminate\Support\Facades\Route;
use App\Services\QuoteService;

Route::get('/api/quote-of-the-day', function (QuoteService $svc) {
    $q = $svc->quoteOfTheDay();
    return response()->json([
        'date' => now('America/Sao_Paulo')->toDateString(),
        'quote' => $q['quote'],
        'author' => $q['author'] ?? null,
        'meta' => [
            'source' => $q['source'] ?? 'local',
            'tags' => $q['tags'] ?? [],
        ],
    ]);
});
