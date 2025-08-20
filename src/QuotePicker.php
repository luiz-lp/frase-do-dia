<?php
declare(strict_types=1);

namespace FraseDoDia;

final class QuotePicker
{
    /** @var array<int, array<string,mixed>> */
    private array $quotes;

    public function __construct(array $quotes)
    {
        if (empty($quotes)) {
            throw new \InvalidArgumentException('Lista de frases vazia.');
        }
        $this->quotes = array_values($quotes);
    }

    /**
     * Seleciona a mesma frase durante todo o dia, baseado em fuso e um "sal".
     * @return array<string,mixed> ['id','quote','author',...]
     */
    public function pickForDate(\DateTimeInterface $date, string $salt = 'v1-rotacao-anual'): array
    {
        $count = count($this->quotes);
        $key = $date->format('Y-m-d') . '|' . $salt; // chave do dia

        // índice determinístico via crc32
        $idx = abs(crc32($key)) % $count;

        return $this->quotes[$idx];
    }

    /**
     * Carrega JSON (UTF-8) e valida formato básico.
     * @return array<int, array<string,mixed>>
     */
    public static function loadJsonFile(string $path): array
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Arquivo não encontrado: $path");
        }
        $raw = file_get_contents($path);
        $data = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($data) || empty($data)) {
            throw new \RuntimeException('JSON inválido ou vazio.');
        }
        return $data;
    }
}
