<?php
header('Content-Type: application/json; charset=utf-8');
$path = __DIR__ . '/../data/quotes.json';
if (!is_file($path)) {
    http_response_code(404);
    echo json_encode(['error' => 'quotes.json não encontrado']);
    exit;
}
readfile($path);
