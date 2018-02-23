<?php

namespace Hokan22\LaravelTranslator\Handler;

use Hokan22\LaravelTranslator\Models\TranslationIdentifier;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Class LocaleHandler
 * @package Hokan22\LaravelTranslator\
 */
class DatabaseHandler implements HandlerInterface
{
    /** @var string The locale to translate to */
    private $locale;
    /** @var array Array with the identifiers as keys and the Texts object as value */
    private $translations;

    /**
     * DatabaseHandler constructor.
     * @param string $locale
     */
    public function __construct($locale) {
        $this->locale = $locale;

        $this->refreshCache();
    }

    /**
     * @return string Return the locale to translate to
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * @param $identifier string Identifier of the translation
     * @param $group string Group for the database query
     * @return string returns the found translation for identifier
     * @throws NotFoundResourceException
     * @throws TranslationNotFoundException
     */
    public function getTranslation($identifier, $group) {

        if(isset($this->translations[$identifier])) {
            if ($this->translations[$identifier]->translation == null) {
                throw new TranslationNotFoundException("The translation for identifier '".$identifier."' and locale '".$this->locale."' could not be found");
            }
            return $this->translations[$identifier]->translation;
        }
        else {
            throw new NotFoundResourceException("The translation identifier '".$identifier."' could not be found");
        }
    }

    /**
     * Refresh the internal Cache
     */
    public function refreshCache() {
        // Get all Texts with translations for the given locale
        $translations =   new TranslationIdentifier();
        $translations =   $translations->leftJoin('translations', function ($join) {
                                                    $join->on( 'translation_identifiers.id', '=', 'translations.translation_identifier_id')
                                                         ->where('locale', $this->locale);
                                                })->get();


        foreach ($translations as $identifier) {
            $this->translations[$identifier->identifier] = $identifier;
        }
    }

    /**
     * @param $group
     * @return mixed
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