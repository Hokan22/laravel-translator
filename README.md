# Laravel Translator 

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

## Install

Via Composer

``` bash
$ composer require hokan22/laravel-translator
```

## Setup

Add the service provider to the providers array in `config/app.php`.

``` php
'providers' => [
    hokan22\LaravelTranslator\Providers\TranslatorServiceProvider::class,
    hokan22\LaravelTranslator\Providers\TranslatorBladeServiceProvider::class,
];
```

Additionally you might want to add an alias to the aliases array within the `config/app.php` file.

``` php
'aliases' => [
    'Translator' => hokan22\LaravelTranslator\Facades\TranslatorFacade::class
];
```

## Middleware

You may want to use the middleware in order to control the global language setup inside `app/Http/Kernel.php`.

``` php
protected $routeMiddleware = [
    'translator' => hokan22\LaravelTranslator\Middleware\TranslatorMiddleware::class,
];
```

## Publishing

You can publish the configuration with:

``` php
php artisan vendor:publish --provider="hokan22\LaravelTranslator\Providers\TranslatorServiceProvider"
```

## Usage

This Package provides a easy extendable translation function with parameters for laravel.

After you registered the TranslatorBladeServiceProvider you can use the ```@t()``` or ```@translate()``` blade directive to translate strings into either the global locale or provide a locale for each string individually. 

``` html
@t('My translation')
@t('Hello {name}', ['name' => world], 'en_US')
@t('Hello World', [], 'de_DE')
```

### Parameters

Parameters are simply surrounded by `{}` and their replacement provided as an array as the second parameter of the blade translate directive.

You can even use `html` within the parameters.
```
@t('Visit the site {link}.', ['link' => '<a href="example.com">here</a>'])
```

### Custom Translation Handler

To use your custom Translation Handler make sure it implements the Interface: ``` Hokan22\LaravelTranslator\Handler\HandlerInterface.php ```
Now just change the 'handler' config parameter in ``` config\translator.php ``` to your custom Handler class.
```
'handler' =>  Hokan22\LaravelTranslator\Handler\DatabaseHandler::class,
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## TODO
    
- blade directive to change locale for more translations
- way to provide group for translation
- blade directive to change group for more locales
- publish tests

## Security

If you discover any security related issues, please email <security@aviertel.de> instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/hokan22/laravel-translator.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/hokan22/laravel-translator/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/hokan22/laravel-translator.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/hokan22/laravel-translator.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/hokan22/laravel-translator.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/hokan22/laravel-translator
[link-travis]: https://travis-ci.org/hokan22/Translator
[link-scrutinizer]: https://scrutinizer-ci.com/g/hokan22/laravel-translator/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/hokan22/laravel-translator
[link-downloads]: https://packagist.org/packages/hokan22/laravel-translator
[link-author]: https://bitbucket.org/hokan22