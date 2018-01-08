<?php

namespace Hokan22\LaravelTranslator\Handler;

use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Custom Exception to distinguish if the Translation Identifier or
 * the Translation was not found for the given locale
 *
 * Class TranslationNotFoundException
 * @package Hokan22\LaravelTranslator\Handler
 */
class TranslationNotFoundException extends \Exception {}

/**
 * Custom Exception to distinguish if the Translation Identifier was not found in Cache but could be in DB
 *
 * Class TranslationNotInCacheException
 * @package Hokan22\LaravelTranslator\Handler
 */
class TranslationNotInCacheException extends TranslationNotFoundException {}

/**
 * Custom Exception thrown when a cache file could not be found
 *
 * Class TranslationCacheNotFound
 * @package Hokan22\LaravelTranslator\Handler
 */
class TranslationCacheNotFound extends \Exception {}

/**
 * Interface HandlerInterface
 * @package Hokan22\LaravelTranslator\Handler
 */
interface HandlerInterface
{
    /**
     * HandlerInterface constructor.
     * @param $locale
     * @throws TranslationNotFoundException
     */
    function __construct($locale);

    /**
     * @param $identifier
     * @param $group
     * @return string
     * @throws NotFoundResourceException
     * @throws TranslationNotFoundException
     */
    function getTranslation($identifier, $group);

    /**
     * Should return the locale currentyl set in the handler
     * @return string
     */
    function getLocale();

    /**
     * Refresh the internal Cache
     * @return void
     */
    function refreshCache();

    /**
     * @param $group
     * @return mixed
     */
    function getAllTranslations($group);

}