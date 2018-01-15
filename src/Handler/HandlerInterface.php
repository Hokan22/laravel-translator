<?php

/**
 * Interface to be implemented by all Handlers
 *
 * @author Alexander Viertel
 * @package Hokan22\LaravelTranslator\Handler
 */
namespace Hokan22\LaravelTranslator\Handler;

use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Custom Exception to distinguish if the Translation Identifier or
 * the Translation was not found for the given locale
 *
 * Class TranslationNotFoundException
 *
 * @category    TranslatorHandler
 * @author      Alexander Viertel
 * @license     MIT
 * @link        https://github.com/Hokan22/laravel-translator
 */
class TranslationNotFoundException extends \Exception {}

/**
 * Custom Exception to distinguish if the Translation Identifier was not found in Cache but could be in DB
 *
 * Class TranslationNotInCacheException
 *
 * @category    TranslatorHandler
 * @author      Alexander Viertel
 * @license     MIT
 * @link        https://github.com/Hokan22/laravel-translator
 */
class TranslationNotInCacheException extends TranslationNotFoundException {}

/**
 * Custom Exception thrown when a cache file could not be found
 *
 * Class TranslationCacheNotFound
 *
 * @category    TranslatorHandler
 * @author      Alexander Viertel
 * @license     MIT
 * @link        https://github.com/Hokan22/laravel-translator
 */
class TranslationCacheNotFound extends \Exception {}

/**
 * Interface HandlerInterface
 *
 * @category    TranslatorHandler
 * @author      Alexander Viertel
 * @license     MIT
 * @link        https://github.com/Hokan22/laravel-translator
 */
interface HandlerInterface
{
    /**
     * HandlerInterface constructor.
     *
     * @param $locale
     *
     * @throws TranslationNotFoundException
     */
    function __construct($locale);

    /**
     * @param string $identifier
     * @param string $group
     *
     * @throws NotFoundResourceException
     * @throws TranslationNotFoundException
     *
     * @return string
     */
    function getTranslation($identifier, $group);

    /**
     * Should return the locale currently set in the handler
     *
     * @return string
     */
    function getLocale();

    /**
     * Refresh the internal Cache
     *
     * @return void
     */
    function refreshCache();

    /**
     * @param string $group
     *
     * @return mixed
     */
    function getAllTranslations($group);

}