<?php

namespace BambooleeDigital\FilamentTranslate\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use BambooleeDigital\FilamentTranslate\Engines\TranslationEngine;

class TranslationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->singleton(TranslationEngine::class, function ($app) {
            $config = $app['config']['filament-translate'];
            $defaultEngine = $config['default_engine'];
            $engineConfig = $config['engines'][$defaultEngine];
            
            if (!isset($engineConfig['class'])) {
                throw new \InvalidArgumentException("Translation engine class not specified for '{$defaultEngine}'");
            }

            $engineClass = $engineConfig['class'];
            
            if (!class_exists($engineClass)) {
                throw new \InvalidArgumentException("Translation engine class '{$engineClass}' does not exist");
            }

            $apiKey = $engineConfig['api_key'] ?? null;
            $availableLocales = $engineConfig['available_locales'] ?? [];

            return new $engineClass($apiKey, $availableLocales);
        });
    }

    public function provides()
    {
        return [TranslationEngine::class];
    }
}
