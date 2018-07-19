<?php

/**
 *
 * Base Handler to extend from
 *
 * @package  Hokan22\LaravelTranslator\Handler
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 */
namespace Hokan22\LaravelTranslator\Handler;

/**
 * Custom Exception to distinguish if the Translation Identifier or
 * the Translation was not found for the given locale
 *
 * Class TranslationNotFoundException
 */
class TranslationNotFoundException extends \Exception {}

/**
 * Custom Exception to distinguish if the Translation Identifier was not found in Cache but could be in DB
 *
 * Class TranslationNotInCacheException
 */
class TranslationNotInCacheException extends TranslationNotFoundException {}

/**
 * Custom Exception thrown when a cache file could not be found
 *
 * Class TranslationCacheNotFound
 */
class TranslationCacheNotFound extends \Exception {}

/**
 * Interface HandlerInterface
 */
class DefaultHandler
{
    /**
     * @var string          $locale         The locale to translate to
     * @var array|array[]   $translations   Array with the identifiers as keys and the Texts object as value
     */
    protected $locale, $translations;

    /**
     * HandlerInterface constructor.
     *
     * @param $locale
     */
    public function __construct($locale) {
        $this->locale = $locale;
    }

    /**
     * Should return the locale currently set in the handler
     *
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Get the translation of a given identifier
     *
     * @param string $identifier
     * @param string $group
     * @return string
     */
    public function getTranslation($identifier, $group) {
        return $identifier;
    }

    /**
     * Refresh the internal Cache
     *
     * @return bool
     */
    public function refreshCache() {
        return true;
    }

    /**
     * Get all translations of a given group
     *
     * @param string $group
     * @return array
     */
    public function getAllTranslations($group) {
        return [];
    }

    /**
     * Get the DB ID of the Identifier
     *
     * @param $identifier
     * @return integer
     */
    public function getDatabaseID($identifier) {
        return 1;
    }

}