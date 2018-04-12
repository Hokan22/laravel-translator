<?php

/**
 * PHP version 5.6
 *
 * Translator Config File.
 *
 * @package  Hokan22\LaravelTranslator\config
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale. It is used, when no locale was given
    | or the given locale is not within available_locales
    |
    */
    'default_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Available Locales
    |--------------------------------------------------------------------------
    |
    | List of available locales.
    | Given locales not within available_locales array will not be translated
    |
    */
    'available_locales' => [
        'en_US',
        'de_DE',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Listening
    |--------------------------------------------------------------------------
    |
    | When the Translator gets Strings which it can not find in the database
    | and listening_enabled is true it will save the Strings to the database
    |
    */
    'listening_enabled' => false,


    /*
    |--------------------------------------------------------------------------
    | Handler
    |--------------------------------------------------------------------------
    |
    | The handler used to get the Translations.
    | The default DatabaseHandler should not be used in a production
    | environment as it needs to query the database for each translation
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
    | NOTE: When using the Live Translation mode make sure the admin edit route
    | is named 'translator.admin.edit'!
    |
    */
    'custom_routes' =>  false,

    /*
    |--------------------------------------------------------------------------
    | Log Level
    |--------------------------------------------------------------------------
    |
    | Set the verbosity of the Translator.
    | Available:
    |       'debug': Log all cached exceptions
    |       'quiet': Don't log cached exceptions
    |
    */
    'log_level' =>  'debug',

];
