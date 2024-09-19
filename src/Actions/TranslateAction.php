<?php

namespace BambooleeDigital\FilamentTranslate\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Spatie\Translatable\HasTranslations;
use Filament\Forms\Components\Actions\Action;
use BambooleeDigital\FilamentTranslate\Engines\TranslationEngineFactory;

class TranslateAction extends Action
{
    use HasTranslations;

    protected array $languages = [];
    protected ?string $activeLocale = null;
    protected ?Field $field = null;

    /**
     * Define as línguas disponíveis para a ação.
     *
     * @param array $languages
     * @return $this
     */
    public function languages(array $languages): static
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Define a língua ativa para a ação.
     *
     * @param string $language
     * @return $this
     */
    public function activeLocale(?string $activeLocale = null): static
    {
        $this->activeLocale = $activeLocale;

        return $this;
    }

    /**
     * Associa a ação a um campo específico.
     *
     * @param Field $field
     * @return $this
     */
    public function field(Field $field): static
    {
        $this->field = $field;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modal()
            ->icon('heroicon-c-language')
            ->label((trans('filament-translate::filament-translate.modal_title')))
            ->form([
                Select::make('engine')
                    ->label((trans('filament-translate::filament-translate.engine')))
                    ->options(function () {
                        return collect(TranslationEngineFactory::getAvailableEngines())
                            ->mapWithKeys(fn($engine) => [$engine => Str::upper($engine)]);
                    })
                    ->required()
                    ->default($this->getDefaultEngine()),

                Select::make('source_locale')
                    ->label((trans('filament-translate::filament-translate.source')))
                    ->options(function () {
                        $engine = $this->getDefaultEngine();
                        $supportedLanguages = $this->getSupportedLanguages($engine);
                        $filledLocales = $this->getFilledLocales();
                        $configuredSourceLanguage = config('filament-translate.default_source_language');

                        $prioritizedLocales = collect([$configuredSourceLanguage])
                            ->merge($filledLocales)
                            ->unique()
                            ->filter()
                            ->values()
                            ->toArray();

                        return collect($supportedLanguages)
                            ->filter(function ($label, $locale) use ($prioritizedLocales) {
                                return in_array($locale, $prioritizedLocales);
                            });
                    })
                    ->default(config('filament-translate.default_source_language'))
                    ->reactive()
                    ->searchable(),

                    Select::make('target_locale')
                    ->label((trans('filament-translate::filament-translate.target')))
                    ->options(function ($get) {
                        $engine = $get('engine');
                        $source = $get('source_locale');
                        return collect($this->getSupportedLanguages($engine))
                            ->filter(fn ($locale, $key) => $key !== $source)
                            ->toArray();
                    })
                    ->default(function ($get) {
                        $engine = $get('engine');
                        $source = $get('source_locale');
                        $supportedLanguages = $this->getSupportedLanguages($engine);
                        
                        // Obter o idioma atual do campo
                        $currentFieldLocale = $this->getCurrentFieldLocale();
                        
                        // Tenta encontrar uma correspondência exata ou de duas letras
                        $match = collect($supportedLanguages)
                            ->filter(fn ($locale, $key) => $key !== $source)
                            ->filter(function ($label, $locale) use ($currentFieldLocale) {
                                return $locale === $currentFieldLocale || 
                                       substr($locale, 0, 2) === substr($currentFieldLocale, 0, 2);
                            })
                            ->keys()
                            ->first();

                        return $match ?? array_key_first($supportedLanguages);
                    })
                    ->searchable()
                    ->required(),
            ])
            ->modalSubmitActionLabel((trans('filament-translate::filament-translate.translate')))
            ->action(function (array $data) {
                $engineName = $data['engine'];
                $sourceLocale = $data['source_locale'];
                $targetLocale = $data['target_locale'];

                try {
                    $translationEngine = TranslationEngineFactory::make($engineName);
                } catch (\Exception $e) {
                    $this->handleError((trans('filament-translate::filament-translate.invalid_engine')));
                    return;
                }

                $record = $this->field->getRecord();
                $attributeName = $this->field->getName();

                $sourceText = $record->getTranslation($attributeName, $sourceLocale);

                if (empty($sourceText)) {
                    $this->handleWarning((trans('filament-translate::filament-translate.no_source_text')));
                    return;
                }

                try {
                    $translatedText = $translationEngine->translate($sourceText, $targetLocale, $sourceLocale);

                    // Atualizar a tradução no modelo
                    $record->setTranslation($attributeName, $targetLocale, $translatedText);
                    $record->save();

                    // Notificar o Filament para atualizar o formulário
                    $this->field->state($translatedText);
                    $this->field->callAfterStateUpdated();

                    Notification::make()
                        ->title((trans('filament-translate::filament-translate.success_title')))
                        ->body((trans('filament-translate::filament-translate.success_message')))
                        ->success()
                        ->send();
                } catch (\Exception $exception) {
                    $this->handleError((trans('filament-translate::filament-translate.error_message') . ' ' . $exception->getMessage()));
                }
            });
    }

    protected function getDefaultEngine(): string
    {
        return config('filament-translate.default_engine');
    }

    protected function getCurrentFieldLocale(): string
    {
        $record = $this->field->getRecord();
        $attributeName = $this->field->getName();

        // Se o registro usar Spatie Translatable
        if ($record instanceof Model && method_exists($record, 'getLocale')) {
            return $record->getLocale();
        }

        // Se o Filament estiver usando um locale específico
        if (method_exists($this->field, 'getLocale')) {
            return $this->field->getLocale();
        }

        // Fallback para o locale da aplicação
        return app()->getLocale();
    }

    protected function handleError($message)
    {
        Log::error($message);
        Notification::make()
            ->title((trans('filament-translate::filament-translate.error_title')))
            ->body($message)
            ->danger()
            ->send();
    }

    protected function handleWarning($message)
    {
        Notification::make()
            ->title((trans('filament-translate::filament-translate.warning_title')))
            ->body($message)
            ->warning()
            ->send();
    }

    protected function getFilledLocales(): array
    {
        $record = $this->field->getRecord();
        $attributeName = $this->field->getName();

        if (method_exists($record, 'getTranslatedLocales')) {
            return $record->getTranslatedLocales($attributeName);
        }

        if (is_array($record->$attributeName)) {
            return array_keys(array_filter($record->$attributeName));
        }

        return [];
    }

    protected function getSupportedLanguages(string $engine): array
    {
        try {
            return TranslationEngineFactory::make($engine)->getSupportedLanguages();
        } catch (\Exception $e) {
            Log::error("Erro ao obter línguas suportadas para a engine '{$engine}': " . $e->getMessage());
            return [];
        }
    }
}
