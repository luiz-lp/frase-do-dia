<?php
require __DIR__ . '/../src/QuoteSources.php';
require __DIR__ . '/../src/QuotePicker.php';
require __DIR__ . '/../src/helpers.php';

use FraseDoDia\QuoteSources;
use FraseDoDia\QuotePicker;
use function FraseDoDia\writePrettyJson;

$path = __DIR__ . '/../data/quotes.json';
$existing = file_exists($path) ? QuotePicker::loadJsonFile($path) : [];
$map = [];
foreach ($existing as $q) {
    $map[mb_strtolower(trim(($q['quote'] ?? '').'|'.($q['author'] ?? '')))] = true;
}

$fetchers = [
    [QuoteSources::class,'fetchFromQuotable'],
    [QuoteSources::class,'fetchFromFavQsQotd'],
    [QuoteSources::class,'fetchFromZenQuotes'],
];

$newOnes = [];
foreach ($fetchers as $fn) {
    $q = @call_user_func($fn);
    if (!$q) continue;
    $key = mb_strtolower(trim(($q['quote'] ?? '').'|'.($q['author'] ?? '')));
    if (!isset($map[$key])) {
        $q['id'] = $q['id'] ?? substr(sha1($key), 0, 10);
        $newOnes[] = $q;
        $map[$key] = true;
    }
}

$merged = array_values(array_merge($existing, $newOnes));
writePrettyJson($path, $merged);

echo "Adicionadas: " . count($newOnes) . PHP_EOL;
