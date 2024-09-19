<?php

namespace BambooleeDigital\FilamentTranslate;

use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Field;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use BambooleeDigital\FilamentTranslate\Actions\TranslateAction;

class FilamentTranslateServiceProvider extends PackageServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $this->registerTranslations();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-translate')
            ->hasConfigFile('filament-translate')
            ->hasViews('filament-translate')
            ->hasTranslations(); // Adiciona suporte a traduções
    }

    public function packageBooted(): void
    {
        // Registrar as engines dinamicamente
        $enginesConfig = config('filament-translate.engines');

        foreach ($enginesConfig as $engineKey => $engineConfig) {
            if (!isset($engineConfig['class'])) {
                throw new \InvalidArgumentException("A configuração da engine '{$engineKey}' está faltando a chave 'class'.");
            }

            $this->app->singleton($engineConfig['class'], function ($app) use ($engineConfig) {
                if (!class_exists($engineConfig['class'])) {
                    throw new \InvalidArgumentException("A classe '{$engineConfig['class']}' para a engine não existe.");
                }

                $apiKey = $engineConfig['api_key'] ?? null;
                $availableLocales = $engineConfig['available_locales'] ?? [];

                if (!$apiKey) {
                    throw new \InvalidArgumentException("A engine '{$engineConfig['name']}' está faltando a chave 'api_key' na configuração.");
                }

                return new $engineConfig['class']($apiKey, $availableLocales);
            });
        }

        // Registrar o macro 'translatable' nos campos
        Field::macro('translatable', function (?string $activeLocale = null) {

            Log::info('Setting up translatable field', [
                'fieldName' => $this,
                'activeLocale' => $activeLocale,
                'languages' => array_keys(config('filament-translate.languages')),
            ]);
            
            /** @var Field $this */
            $this->suffixActions([
                TranslateAction::make($this->getName())
                    ->activeLocale($activeLocale)
                    ->languages(array_keys(config('filament-translate.languages')))
                    ->field($this),
            ]);

            return $this;
        });
    }

    public function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-translate');
    
        // Permitir publicação das traduções
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/filament-translate'),
            ], 'filament-translate-lang');
        }
    }
}
