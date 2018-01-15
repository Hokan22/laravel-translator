<?php

/**
 * Translator
 *
 * @package Hokan22\LaravelTranslator
 * 
 * @author Alexander Viertel <alexander@aviertel.de>
 */
namespace Hokan22\LaravelTranslator;

use Illuminate\Support\Facades\Facade;

/**
 * Class TranslatorFacade
 *
 * @package Hokan22\LaravelTranslator
 *
 * @category Translator
 * @author Alexander Viertel <alexander@aviertel.de>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://github.com/Hokan22/laravel-translator
 *
 * @method static string    getConfigValue($key)
 * @method static string    translate($identifier , $parameters = null, $locale = '')
 * @method static void      setLocale($key)
 * @method static void      addMissingIdentifier($identifier, $parameters, $group)
 * @method static boolean   hasIdentifier($identifier)
 * @method static string    validateLocale($locale)
 * @method static string    getAllTranslations($locale, $group)
 */
class TranslatorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Translator';
    }
}
