<?php

namespace BambooleeDigital\FilamentTranslate\Engines;

use DeepL\Translator;

class DeepLEngine implements TranslationEngine
{
    protected Translator $translator;
    protected array $availableLocales;

    public function __construct(string $apiKey, array $availableLocales = [])
    {
        $this->translator = new Translator($apiKey);
        $this->availableLocales = $availableLocales;
    }

    public function translate(string $text, string $targetLanguage, string $sourceLanguage = null): string
    {
        $result = $this->translator->translateText($text, $sourceLanguage, $targetLanguage);
        return $result->text;
    }

    public function getSupportedLanguages(): array
    {
        return $this->availableLocales;
    }
}
