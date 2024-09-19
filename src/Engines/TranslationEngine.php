<?php

namespace BambooleeDigital\FilamentTranslate\Engines;

interface TranslationEngine
{
    /**
     * Traduz o texto fornecido para o idioma de destino.
     *
     * @param string      $text           Texto a ser traduzido.
     * @param string      $targetLanguage Código do idioma de destino (ex: 'en-US').
     * @param string|null $sourceLanguage Código do idioma de origem (opcional).
     *
     * @return string Texto traduzido.
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = null): string;

    /**
     * Retorna uma lista de idiomas suportados pelo engine.
     *
     * @return array Lista de códigos de idiomas suportados.
     */
    public function getSupportedLanguages(): array;
}
