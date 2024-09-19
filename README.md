# Filament Translate

Filament Translate is a powerful and flexible package that adds translation capabilities to your Filament PHP admin panel. It allows you to easily translate fields directly within the Filament interface.

## Requirements

- PHP 8.0+
- Laravel 8.0+
- Filament PHP 2.0+
- Spatie Laravel Translatable

## Installation

1. Install the package via Composer:

```bash
composer require bamboolee-digital/filament-translate
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-translate-config"
```

3. Publish the translation files (optional, but recommended for customization):

```bash
php artisan vendor:publish --tag="filament-translate-translations"
```

4. Add the `Spatie\Translatable\HasTranslations` trait to your model:

```php
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'content'];
}
```

## Configuration

In the `config/filament-translate.php` file, you can configure:

```php
return [
    'default_engine' => 'deepl',
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
        // Add other engines here
    ],
    'languages' => [
        'en-US' => 'English (American)',
        'es-ES' => 'Spanish (Spain)',
        'pt-BR' => 'Portuguese (Brazil)',
        // Add more languages as needed
    ],
    'default_source_language' => null,
    'source_locale_strategy' => 'dynamic', // or 'fixed'
];
```

## Usage

### Making a Field Translatable

To make a field translatable in your Filament resource, use the `translatable()` method:

```php
use Filament\Resources\Form;
use Filament\Forms\Components\TextInput;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('title')
                ->translatable()
                ->required(),
            // ... other fields
        ]);
}
```

### Specifying an Active Locale

You can specify an active locale when using the `translatable()` method:

```php
TextInput::make('title')
    ->translatable(activeLocale: 'en')
```

The `activeLocale` determines which translation will be initially displayed in the field.

## How It Works

When you call `->translatable()` on a field, the package adds an action button next to the field. Clicking this button opens a modal, allowing you to translate the field's content into different languages using the configured translation engine.

## Customizing Translations

You can customize the translation strings used by the package. After publishing the translation files, you can edit them in `resources/lang/vendor/filament-translate`.

For example, to customize the English translations, edit the file `resources/lang/vendor/filament-translate/en/filament-translate.php`:

```php
return [
    'modal_title' => 'Translate Content',
    'engine' => 'Translation Service',
    'source' => 'Original Language',
    'target' => 'Target Language',
    'translate' => 'Translate Now',
    // ... other translations
];
```

## Creating a Custom Translation Engine

Here's an example of how to create a Google Translate engine:

1. Create a new class that implements the `TranslationEngine` interface:

```php
use BambooleeDigital\FilamentTranslate\Contracts\TranslationEngine;
use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslateEngine implements TranslationEngine
{
    protected TranslateClient $client;

    public function __construct(string $apiKey)
    {
        $this->client = new TranslateClient([
            'key' => $apiKey
        ]);
    }

    public function translate(string $text, string $targetLanguage, ?string $sourceLanguage = null): string
    {
        $result = $this->client->translate($text, [
            'target' => $targetLanguage,
            'source' => $sourceLanguage,
        ]);

        return $result['text'];
    }

    public function getSupportedLanguages(): array
    {
        $languages = $this->client->languages();
        return array_combine($languages, $languages);
    }
}
```

2. Register your custom engine in the config file:

```php
'engines' => [
    'google' => [
        'name' => 'Google Translate',
        'class' => \App\TranslationEngines\GoogleTranslateEngine::class,
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'available_locales' => [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            // Add more as needed
        ],
    ],
],
```

3. Set it as the default engine or use it selectively in your fields:

```php
TextInput::make('title')
    ->translatable()
    ->translateEngine('google')
```

## Complete Example

Here's a complete example of using Filament Translate in a Filament resource:

```php
use App\Models\Post;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->translatable(activeLocale: app()->getLocale()),
                
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->translatable(),
                
                Forms\Components\TextInput::make('author')
                    ->required(),
                
                // This field won't be translatable
                Forms\Components\DatePicker::make('published_at'),
            ]);
    }

    // ... other resource methods
}
```

In this example:
- The 'title' and 'content' fields are translatable.
- The 'title' field uses the current application locale as the active locale.
- The 'content' field uses the package's default locale.
- The 'author' and 'published_at' fields are not translatable.

## Troubleshooting

If translations are not working:

1. Ensure the `HasTranslations` trait is properly added to your model.
2. Verify that the fields are listed in the `$translatable` array in your model.
3. Check that the correct languages are set in your `config/filament-translate.php` file.
4. Confirm your translation engine (e.g., DeepL) is properly configured with a valid API key.
5. Clear Laravel's cache: `php artisan cache:clear`
6. If package translation strings are not appearing, try using `trans('filament-translate::filament-translate.key')` instead of `__()`.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Author

Kellvem Barbosa (kellvembarbosa)# filament-translate
