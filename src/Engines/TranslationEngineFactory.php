<?php

namespace BambooleeDigital\FilamentTranslate\Engines;

use Illuminate\Support\Facades\App;

class TranslationEngineFactory
{
    /**
     * Instancia a engine de tradução com base no nome fornecido.
     *
     * @param string $engine
     * @return TranslationEngine
     *
     * @throws \InvalidArgumentException
     */
    public static function make(string $engine): TranslationEngine
    {
        $enginesConfig = config('filament-translate.engines');

        if (!array_key_exists($engine, $enginesConfig)) {
            throw new \InvalidArgumentException("Engine de tradução '{$engine}' não é suportada.");
        }

        $engineClass = $enginesConfig[$engine]['class'];

        return App::make($engineClass);
    }

    /**
     * Retorna uma lista das engines disponíveis.
     *
     * @return array
     */
    public static function getAvailableEngines(): array
    {
        return array_keys(config('filament-translate.engines', []));
    }
}
