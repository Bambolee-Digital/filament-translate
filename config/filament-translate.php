<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Engine de Tradução Padrão
    |--------------------------------------------------------------------------
    |
    | Define o engine de tradução padrão que será utilizado pelo plugin.
    | Você pode trocar para qualquer outro engine implementado.
    |
    */

    'default_engine' => 'deepl', // Agora é uma string referenciando o key em 'engines'

    /*
    |--------------------------------------------------------------------------
    | Configurações dos Engines
    |--------------------------------------------------------------------------
    |
    | Defina aqui as configurações específicas para cada engine de tradução.
    |
    */

    'engines' => [
        'deepl' => [
            'name' => 'DeepL',
            'class' => \BambooleeDigital\FilamentTranslate\Engines\DeepLEngine::class,
            'api_key' => env('DEEPL_API_KEY'),
            'available_locales' => [
                'en-us' => 'English (American)',
                'es' => 'Spanish (Spain)',
                'pt-br' => 'Portuguese (Brazil)',
            ],
        ],

        // Exemplo de outro engine
        // 'google' => [
        //     'name' => 'Google Translate',
        //     'class' => \BambooleeDigital\FilamentTranslate\Engines\GoogleTranslateEngine::class,
        //     'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        //     'available_locales' => [
        //         // Defina os locais suportados
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Idiomas Suportados
    |--------------------------------------------------------------------------
    |
    | Lista de idiomas que serão disponibilizados para tradução.
    |
    */

    'languages' => [
        'en-US' => 'English (American)',
        'es-ES' => 'Spanish (Spain)',
        'pt-BR' => 'Portuguese (Brazil)',
        // Adicione mais idiomas conforme necessário
    ],

    /*
    |--------------------------------------------------------------------------
    | Idioma de Origem Padrão
    |--------------------------------------------------------------------------
    |
    | O idioma de origem padrão para as traduções. Deixe como null para detecção automática.
    |
    */

    'default_source_language' => null,

    'default_source_language' => 'pt',

    'source_locale_strategy' => 'dynamic', // ou 'fixed'
];
