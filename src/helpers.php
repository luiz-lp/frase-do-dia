<?php
namespace FraseDoDia;

function nowSaoPaulo(): \DateTimeImmutable {
    return new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
}

function readOrNull(string $path): ?array {
    return is_file($path) ? json_decode((string)file_get_contents($path), true) : null;
}

function writePrettyJson(string $path, array $payload): void {
    @is_dir(dirname($path)) || @mkdir(dirname($path), 0775, true);
    file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
}
