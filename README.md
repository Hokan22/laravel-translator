# Laravel Translator 

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Scrutinizer Code Quality][ico-code-quality]][link-code-quality]

## Install

Via Composer

``` bash
$ composer require hokan22/laravel-translator
```

## Setup

Add the service provider to the providers array in `config/app.php`.

``` php
'providers' => [
    Hokan22\LaravelTranslator\Provider\TranslatorProvider::class,
    Hokan22\LaravelTranslator\Provider\TranslatorBladeProvider::class,
];
```

Additionally you might want to add an alias to the aliases array within the `config/app.php` file.

``` php
'aliases' => [
    'Translator' => Hokan22\LaravelTranslator\TranslatorFacade::class,
];
```

## Middleware

You may want to use the middleware in order to control the global language setup inside `app/Http/Kernel.php`.

``` php
protected $routeMiddleware = [
    'translator' => \Hokan22\LaravelTranslator\Middleware\TranslatorMiddleware::class,
];
```

## Publishing

You can publish the configuration with:

``` php
php artisan vendor:publish --provider="Hokan22\LaravelTranslator\Provider\TranslatorProvider"
```

## Usage

This Package provides an easily extendable translation function with parameters for laravel.

After you registered the TranslatorBladeServiceProvider you can use the ```@t()``` or ```@translate()``` blade directives to translate your website into different languages.
You can define a locale through the translator middleware or define a locale for each translation individually.

```
@t('Hello World!')
@t('Hello {name}!', ['name' => World], 'de_DE')
@t('Hello World', [], 'fr_FR')
```

### Parameters

Parameters are simply surrounded by `{}` and their replacement provided as an array as the second parameter of the blade translate directive.

```
@t('Visit the site {link}.', ['link' => '<a href="example.com">Example.com</a>'])
```

### Custom Locales

If you use a different locale schema, just change the ```available_locales``` array in the config file.

### Custom Translation Handler

To use your custom Translation Handler make sure it implements the Interface: ``` Hokan22\LaravelTranslator\Handler\HandlerInterface.php ```
Now just change the 'handler' config parameter in ``` config\translator.php ``` to your custom Handler class.
``` php
'handler' =>  Hokan22\LaravelTranslator\Handler\DatabaseHandler::class,
```

### Custom Translation Routes

By default the Translator admin Interface is reachable under ```/translator/admin```.
To override the default routes change the ```custom_routes``` parameter in the config to ```true``` and define the routes as you need them.
NOTE: In Order to use the "Live Mode" make sure you give the route to ```TranslatorAdminController@edit```  the name: ```'translator.admin.edit'```.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

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
