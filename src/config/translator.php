<?php

/**
 * PHP version 5.6
 *
 * Config File for Translator.
 *
 * @category Config
 * @package  Hokan22\LaravelTranslator\config
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/Hokan22/laravel-translator
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale. It is used, when no other locale was defined,
    | the defined locale is not within available_locales or
    | no translation for the given string and locale was found
    */

    'default_locale' => 'de_DE',

    /*
    |--------------------------------------------------------------------------
    | Available Locales
    |--------------------------------------------------------------------------
    |
    | List of available locales.
    | When a Text has a Translation for a locale not specified below it will
    | not be translated, because the Translator first checks if a given locale
    | is valid, by comparing them with the locales below.
    |
    */

    'available_locales' => [
        'de_DE',
        'en_US',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Listening
    |--------------------------------------------------------------------------
    |
    | When listening is enable and the APP_ENV is not production
    | missing translation will be added to the Database
    |
    */

    'listening_enabled' => false,


    /*
    |--------------------------------------------------------------------------
    | Handler
    |--------------------------------------------------------------------------
    |
    | The handler (MVC Model) to use when translating text
    | The handler MUST implement Translator\Handler\HandlerInterface.php
    |
    */
    'handler' =>  Hokan22\LaravelTranslator\Handler\DatabaseHandler::class,

    /*
    |--------------------------------------------------------------------------
    | Cache Path
    |--------------------------------------------------------------------------
    |
    | Base path where the locale folders with the Cache files should be stored
    | Default is : storage_path('framework/cache/lang/')
    |
    | <$cache_path>
    |       ├── de_DE
    |       │   ├── default.php
    |       │   ├── js.php
    |       │   └── <$group>.php
    |       │    ...
    |       └── <$locale>
    |       .   ├── default.php
    |       .   ├── js.php
    |       .   └── <$group>.php
    |            ...
    */
    'cache_path' => storage_path('framework/cache/lang/'),


    /*
    |--------------------------------------------------------------------------
    | Custom Routes
    |--------------------------------------------------------------------------
    |
    | If set to false the Translator Routes for Admin Interface and Test View,
    | defined in the Translators route.php will be used
    |
    */
    'custom_routes' =>  false,

];
