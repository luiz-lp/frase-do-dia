<?php
require __DIR__ . '/../src/QuotePicker.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/QuoteSources.php';
require __DIR__ . '/../src/Translator.php';

use FraseDoDia\QuotePicker;
use FraseDoDia\QuoteSources;
use function FraseDoDia\nowSaoPaulo;
use function FraseDoDia\readOrNull;
use function FraseDoDia\writePrettyJson;

$today = nowSaoPaulo();
$cachePath = __DIR__ . '/../cache/qotd.json';

// 1) cache diário
$cache = readOrNull($cachePath);
if ($cache && ($cache['date'] ?? null) === $today->format('Y-m-d')) {
    $q = $cache['quote'];
} else {
    // 2) tenta fontes externas
    $q = QuoteSources::fetchFromQuotable()
        ?? QuoteSources::fetchFromFavQsQotd()
        ?? QuoteSources::fetchFromZenQuotes();

    // 3) se tudo falhar, cai para local
    if (!$q) {
        $quotes = QuotePicker::loadJsonFile(__DIR__ . '/../data/quotes.json');
        $picker = new QuotePicker($quotes);
        $q = $picker->pickForDate($today);
        $q['source'] = $q['source'] ?? 'local';
    }


    // tradução opcional p/ pt-BR quando vier em inglês
    if (($q['lang'] ?? 'en') !== 'pt') {
        $translated = \FraseDoDia\Translator::translateLibre($q['quote'] ?? '', $q['lang'] ?? 'en', 'pt');
        if ($translated) {
            $q['quote'] = $translated;
            $q['lang'] = 'pt';
            $q['source'] = ($q['source'] ?? 'externo') . '+libretranslate';
        }
    }
    writePrettyJson($cachePath, ['date' => $today->format('Y-m-d'), 'quote' => $q]);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'date' => $today->format('Y-m-d'),
    'timezone' => 'America/Sao_Paulo',
    'quote' => $q['quote'] ?? null,
    'author' => $q['author'] ?? null,
    'meta' => [
        'id' => $q['id'] ?? null,
        'source' => $q['source'] ?? 'local',
        'tags' => $q['tags'] ?? [],
    ],
], JSON_UNESCAPED_UNICODE);
