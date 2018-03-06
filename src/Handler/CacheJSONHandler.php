<?php

/**
 * Translation handler for cached translations in JSON Format
 */
namespace Hokan22\LaravelTranslator\Handler;

use Hokan22\LaravelTranslator\TranslatorFacade;

/**
 * Class CacheJSONHandler
 *
 * @category TranslatorHandler
 * @package  Hokan22\LaravelTranslator\Handler
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/Hokan22/laravel-translator
 */
class CacheJSONHandler implements HandlerInterface
{
    /**
     * @var string $locale The locale to translate to
     * @var array|array[] $translations Array with the identifiers as keys and the Texts object as value
     */
    protected $locale, $translations;

    /**
     * DatabaseHandler constructor.
     *
     * @param string $locale The locale of the translations
     * @throws TranslationCacheNotFound
     */
    public function __construct($locale)
    {
        $this->locale   = $locale;

        $this->refreshCache();
    }

    /**
     * Returns the currently set locale
     *
     * @return string Return the locale to translate to
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Returns the translation for the given identifier
     *
     * @param string $identifier Identifier for the database query
     * @param string $group
     * @throws TranslationNotInCacheException
     * @return string returns the found translation for locale and identifier
     */
    public function getTranslation($identifier, $group = 'default')
    {
        // Return translation if found otherwise return TranslationNotInCacheException
        // NOTE: This should never trigger the addition of the identifier to the database,
        // because the cache will not be updated automatically.
        // So not finding the same identifier twice in the cache, will result in an error.
        if (isset($this->translations[$group][$identifier])) {
            return $this->translations[$group][$identifier];
        }
        else {
            throw new TranslationNotInCacheException("The translation identifier '".$identifier."' could not be found in Cache");
        }
    }

    /**
     * Refresh the internal Cache
     *
     * @param string $group
     * @throws TranslationCacheNotFound
     */
    public function refreshCache($group = 'default')
    {
        // Construct the cache folder path from the cache base path defined in the config and the given locale
        $locale_dir = TranslatorFacade::getConfigValue('cache_path').$this->locale;

        // If a Group is defined just get the translations from that group
        try {
             $trans_identifier = json_decode(file_get_contents($locale_dir.'/'.$group.'.json'), true);
        } catch (\ErrorException $e) {
            throw new TranslationCacheNotFound("The Translation cache file '".$locale_dir.'/'.$group.'.json'."' could not be found!");
        }

        // Set class Attribute to constructed array
        $this->translations[$group] = $trans_identifier;
    }

    /**
     * Get all translation of $group
     *
     * @param string $group Group of the translations to return
     * @throws TranslationCacheNotFound
     * @return array|mixed Translations of the given group
     */
    public function getAllTranslations($group)
    {
        if (!isset($this->translations[$group])) {
            $this->refreshCache($group);
        }
        return $this->translations[$group];
    }
}