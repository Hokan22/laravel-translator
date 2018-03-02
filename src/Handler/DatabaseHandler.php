<?php

/**
 * PHP version 5.6
 *
 * Translation handler for cached translations in JSON Format
 *
 * @category TranslatorHandler
 * @package  Hokan22\LaravelTranslator\Handler
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/Hokan22/laravel-translator
 */
namespace Hokan22\LaravelTranslator\Handler;

use Hokan22\LaravelTranslator\Models\TranslationIdentifier;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Class LocaleHandler
 *
 * @category TranslatorHandler
 * @package  Hokan22\LaravelTranslator\Handler
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/Hokan22/laravel-translator
 */
class DatabaseHandler implements HandlerInterface
{
    /**
     * @var string          $locale         The locale to translate to
     * @var array|array[]   $translations   Array with the identifiers as keys and the Texts object as value
     */
    protected $locale, $translations;

    /**
     * DatabaseHandler constructor.
     *
     * @param string $locale The locale of the translations
     */
    public function __construct($locale)
    {
        $this->locale = $locale;

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
     * @param string $identifier    Identifier of the translation
     * @param string $group         Group for the database query
     *
     *
     * @throws NotFoundResourceException
     * @throws TranslationNotFoundException
     *
     * @return string returns the found translation for identifier
     */
    public function getTranslation($identifier, $group)
    {

        if (isset($this->translations[$identifier])) {
            if ($this->translations[$identifier]->translation == null) {
                throw new TranslationNotFoundException("The translation for identifier '".$identifier."' and locale '".$this->locale."' could not be found");
            }
            return $this->translations[$identifier]->translation;
        } else {
            throw new NotFoundResourceException("The translation identifier '".$identifier."' could not be found");
        }
    }

    /**
     * Refresh the internal Cache
     *
     * @return void
     */
    public function refreshCache()
    {
        // Get all Texts with translations for the given locale
        $translations = new TranslationIdentifier();
        $translations = $translations->leftJoin('translations', function ($join)
            {
                $join->on( 'translation_identifiers.id', '=', 'translations.translation_identifier_id')
                     ->where('locale', $this->locale);
            }
            )->get();

        foreach ($translations as $identifier) {
            $this->translations[$identifier->identifier] = $identifier;
        }
    }

    /**
     * Get all translation of $group
     *
     * @param string $group Group of the translations to return
     *
     * @return array|mixed Translations of the given group
     */
    public function getAllTranslations($group = 'default')
    {
        $return = [];
        foreach (collect($this->translations)->where('group', $group) as $key => $translation) {
            if ($translation->translation == null) {
                $return[$key] = $translation->identifier;
            } else {
                $return[$key] = $translation->translation;
            }
        }
        return $return;
    }

    /**
     * @param $identifier
     * @return integer
     * @throws NotFoundResourceException
     */
    function getDatabaseID($identifier)
    {
        if(isset($this->translations[$identifier])) {
            return $this->translations[$identifier]->id;
        } else {
            throw new NotFoundResourceException("The translation identifier '".$identifier."' could not be found");
        }
    }
}