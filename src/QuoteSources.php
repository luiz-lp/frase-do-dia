<?php
declare(strict_types=1);

namespace FraseDoDia;

final class QuoteSources
{
    /** Quotable: aleatória */
    public static function fetchFromQuotable(): ?array
    {
        $resp = @file_get_contents('https://api.quotable.io/random');
        if (!$resp) return null;
        $json = json_decode($resp, true);
        if (!isset($json['content'])) return null;

        return [
            'id' => $json['_id'] ?? null,
            'quote' => $json['content'],
            'author' => $json['author'] ?? null,
            'source' => 'quotable',
            'tags' => $json['tags'] ?? [],
            'lang' => 'en'
        ];
    }

    /** FavQs: quote of the day (sem token) */
    public static function fetchFromFavQsQotd(): ?array
    {
        $resp = @file_get_contents('https://favqs.com/api/qotd');
        if (!$resp) return null;
        $json = json_decode($resp, true);
        if (!isset($json['quote']['body'])) return null;

        return [
            'id' => $json['quote']['id'] ?? null,
            'quote' => $json['quote']['body'],
            'author' => $json['quote']['author'] ?? null,
            'source' => 'favqs',
            'tags' => [],
            'lang' => 'en'
        ];
    }

    /** ZenQuotes: aleatória */
    public static function fetchFromZenQuotes(): ?array
    {
        $resp = @file_get_contents('https://zenquotes.io/api/random');
        if (!$resp) return null;
        $json = json_decode($resp, true);
        if (!is_array($json) || empty($json[0]['q'])) return null;

        return [
            'id' => null,
            'quote' => $json[0]['q'],
            'author' => $json[0]['a'] ?? null,
            'source' => 'zenquotes',
            'tags' => [],
            'lang' => 'en'
        ];
    }
}
