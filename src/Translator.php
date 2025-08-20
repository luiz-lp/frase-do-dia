<?php
namespace FraseDoDia;

final class Translator
{
    /**
     * Tenta traduzir usando LibreTranslate (self-host/saas).
     * Configuração via env:
     *  - LT_URL (ex: https://libretranslate.com/translate)
     *  - LT_API_KEY (opcional)
     */
    public static function translateLibre(string $text, string $source = 'en', string $target = 'pt'): ?string
    {
        $url = getenv('LT_URL') ?: null;
        if (!$url) return null;

        $payload = http_build_query([
            'q' => $text,
            'source' => $source,
            'target' => $target,
            'format' => 'text',
            'api_key' => getenv('LT_API_KEY') ?: ''
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded
",
                'content' => $payload,
                'timeout' => 6
            ]
        ]);

        $resp = @file_get_contents($url, false, $ctx);
        if (!$resp) return null;
        $json = json_decode($resp, true);
        return $json['translatedText'] ?? null;
    }
    /**
     * Google Cloud Translation API v2 (Basic)
     * ENV:
     *  - GCP_TRANSLATE_KEY
     *  - GCP_TRANSLATE_URL (opcional, default v2)
     */
    public static function translateGoogle(string $text, string $source = 'auto', string $target = 'pt'): ?string
    {
        $key = getenv('GCP_TRANSLATE_KEY') ?: null;
        if (!$key) return null;

        $url = rtrim(getenv('GCP_TRANSLATE_URL') ?: 'https://translation.googleapis.com/language/translate/v2', '/');

        $params = [
            'key'    => $key,
            'q'      => $text,                 // pode virar array de strings
            'target' => $target,
        ];

        if ($source !== 'auto') {
            $params['source'] = $source;
        }

        $payload = http_build_query($params);

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 8
            ]
        ]);

        $resp = @file_get_contents($url, false, $ctx);
        if (!$resp) return null;

        $json = json_decode($resp, true);
        // Resposta: { data: { translations: [ { translatedText: "..." } ] } }
        return $json['data']['translations'][0]['translatedText'] ?? null;
    }
}
