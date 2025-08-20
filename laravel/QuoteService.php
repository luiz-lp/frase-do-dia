<?php
namespace App\Services;

use FraseDoDia\QuotePicker;
use function FraseDoDia\nowSaoPaulo;

class QuoteService
{
    private QuotePicker $picker;

    public function __construct()
    {
        $dataPath = base_path('data/quotes.json');
        $this->picker = new QuotePicker(QuotePicker::loadJsonFile($dataPath));
    }

    public function quoteOfTheDay(string $salt = 'v1-rotacao-anual'): array
    {
        return $this->picker->pickForDate(nowSaoPaulo(), $salt);
    }
}
