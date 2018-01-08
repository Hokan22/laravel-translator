<?php
/*
 * File:     TranslatorFacade.php
 * Category: -
 * Author:   alexander
 * Created:  22.11.17 16:23
 * Updated:  -
 *
 * Description:
 *  -
 */


namespace Hokan22\LaravelTranslator;

use Illuminate\Support\Facades\Facade;

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
