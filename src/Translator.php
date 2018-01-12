<?php

namespace Hokan22\LaravelTranslator;

use Hokan22\LaravelTranslator\Handler\DatabaseHandler;
use Hokan22\LaravelTranslator\Handler\HandlerInterface;
use Hokan22\LaravelTranslator\Handler\TranslationNotFoundException;
use Hokan22\LaravelTranslator\Models\TranslationIdentifier;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Translation\Exception\NotFoundResourceException;


/**
 * Class Translator
 * @package Hokan22\LaravelTranslator\
 */
class Translator
{
    /** @var array|HandlerInterface[] Class to Handle the Translation defined in config */
    private $aHandler = [];
    /** @var string The locale to translate to  */
    private $locale = '';
    /** @var string The translation group */
    private $group = 'default';
    /** @var string The name of the config file */
    private $configName = 'translator';
    /** @var array */
    private $config;

    /**
     * Translator constructor.
     * @param string $locale
     * @throws \Exception
     */
    public function __construct($locale = '') {
        $this->config = Config::get($this->configName);

        $this->setLocale($locale);
    }

    /**
     * @param $key
     * @return string|array
     */
    public function getConfigValue($key) {
        return $this->config[$key];
    }

    /**
     * @param $identifier
     * @param array|null $parameters
     * @param string $locale
     * @return string
     * @throws \Exception
     * TODO: Make function Parameters interchangeable
     */
    public function translate($identifier , $parameters = null, $locale = '') {

        // Validate the locale given as parameter or take the saved locale
        if ($locale !== '' || $this->locale === '') {
            $locale = $this->validateLocale($locale);
        } else {
            $locale = $this->locale;
        }

        //Create a Handler if no one exists for the current locale
        if (!isset($this->aHandler[$locale])) {
            $this->aHandler[$locale] = $this->createHandler($locale);
        }

        // Try getting the resulting Translation
        // Based on the internal translate function, the getTranslation can throw exceptions
        try {
            $translation = $this->aHandler[$locale]->getTranslation($identifier, $this->group);
        }
        // Thrown when the Identifier wasn't found
        catch (NotFoundResourceException $exception) {
            // Log exception as error in Laravel log
            Log::error($exception);

            // Listener: When app is not in production and listening is enabled
            // add any missing translation identifier to the database
            if ($this->config['listening_enabled'] === true) {
                $this->addMissingIdentifier($identifier, $parameters, 'default');
            }

            return $this->returnMissingTranslation($identifier, $locale);
        }
        // Thrown when no translation for the locale was found
        catch (TranslationNotFoundException $exception) {
            // Log exception as error in Laravel log
            Log::error($exception);

            return $this->returnMissingTranslation($identifier, $locale);
        }

        // If there are no parameters, skip replacement
        if (is_array($parameters)) {
            $translation = $this->replaceParameter($translation, $parameters);
        }

        // Return the translation
        return $translation;
    }

    /**
     * Sets the Handler
     * @param $locale
     * @return HandlerInterface
     */
    private function createHandler($locale) {
        // Get the Handler class from config file
        $handler_class = $this->config['handler'];
        // Define message as empty for later check
        $oHandler = null;

        // Try to create new Instance of Handler and return it
        // If creating the Handler fails or it does not implement HandlerInterface the DatabaseHandler will be used
        try {
            $oHandler = new $handler_class($locale);
            if (!is_a($handler_class, 'Hokan22\LaravelTranslator\Handler\HandlerInterface', TRUE)) {
                throw new \Exception($handler_class . ' does not implement HandlerInterface!');
            }
        }
        catch (\Exception $exception) {
            // Log error and fallback procedure
            Log::error($exception);
            Log::warning('Falling back to DatabaseHandler');

            // Fallback to Database Handler
            $oHandler = new DatabaseHandler($locale);
        }

        return $oHandler;
    }

    /**
     * @param $locale
     */
    public function setLocale($locale) {
        $locale = $this->validateLocale($locale);

        if (!isset($this->aHandler[$locale])) {
            $this->aHandler[$locale] = $this->createHandler($locale);
        }

        $this->locale = $locale;
    }

    /**
     * Add the missing identifier to the texts table in the database
     * @param $identifier
     * @param $parameters
     * @param $group
     */
    public function addMissingIdentifier($identifier, $parameters, $group) {

        if(! $this->hasIdentifier($identifier)) {

            // Save only the keys from the parameter array
            $keys = [];
            if (is_array($parameters)) {
                foreach($parameters as $key => $value) {
                    $keys[] = $key;
                }
            }

            // Create new TranslationIdentifier with parameters and current url
            TranslationIdentifier::create([
                        "identifier"    => $identifier,
                        "parameters"    => $keys,
                        "group"         => isset($group) ? $group : 'default',
                        "page_name"     => app()->runningInConsole() ? '' : substr(request()->getRequestUri(), 1),
                    ]);

            if (isset($this->aHandler[$this->locale])) {
                // refresh the Cache for the handler
                // When using file Cache, adding the Identifier to the Database will not add it to file Cache!
                $this->aHandler[$this->locale]->refreshCache();
            }

            // Print notice about creation to laravel log
            Log::notice('The translation string "'.$identifier.'" will be written to the Database');
        }
        else {
            Log::warning('The translation string "'.$identifier.'" is already in the Database!');
        }
    }

    /**
     * Check if the identifier exists in the database
     * @param $identifier
     * @return boolean
     */
    public function hasIdentifier($identifier) {
        // Returns true if at least one identifier was found
        return TranslationIdentifier::where('identifier', $identifier)->count() > 0;
    }

    /**
     * @param $translation
     * @param $parameters
     * @return string
     */
    private function replaceParameter ($translation, $parameters) {

        // Go through each specified Parameter and replace its placeholder "{$key}"
       foreach ($parameters as $key => $parameter) {
            // TODO: Make Prefix and Suffix configurable
            // If the string (e.g "{name}") is not specified within the "parameters" array it won't be replaced!
            $translation = str_replace("{".$key."}", $parameter, $translation);
        }

        return $translation;
    }

    /**
     * Instead of the translation echo back 'Missing Translation' (when not in production!)
     * and show the translation identifier ($identifier) and the locale
     *
     * @param $identifier
     * @param $locale
     * @return string
     * @throws \Exception
     */
    private function returnMissingTranslation ($identifier, $locale) {

        // Return identifier and locale for easier debug
        if (config('app.env') !== 'production') {
            return '&lt;'.$identifier.':'.$locale.'&gt;';
        }
        return $identifier;
    }

    /**
     * Checks the given locale and returns a valid local
     * If no locale was given, first try the locale from the session
     * If the Session has no
     *
     * @param string $locale
     * @throws NotFoundResourceException
     * @return string
     */
    public function validateLocale($locale) {

        //Get Locales configs from translator config file
        $avail_locales      = $this->config['available_locales'];
        $default_locale     = $this->config['default_locale'];

        // If locale is already set and not empty it has already been checked
        if ($this->locale == $locale && $this->locale !== '') {
            return $locale;
        }

        // Fallback if empty locale was given (should be handled in middleware)
        if ($locale == ''){
            if (session()->get('locale') != '') {
                $locale = session()->get('locale');
            }
            else {
                return $default_locale;
            }
        }

        // If the given locale is not defined as valid, try to get a fallback locale
        if (!in_array($locale, $avail_locales)){

            $found_locales = [];

            // Find any available locale which contains the locale as substring
            foreach ($avail_locales as $avail_locale) {
                if (strpos($avail_locale, $locale) !== false){
                    $found_locales[] = $avail_locale;
                }
            }

            // Check if default locale is inside the found locales. If it was, use it!
            if (in_array($default_locale, $found_locales)){
                Log::warning('Locale "'.$locale.'" was not found! Falling back to default locale "'.$default_locale.'"');
                $locale = $default_locale;
            }
            // Check if any Locale containing '$locale' was found previously
            elseif (count($found_locales, 0) >= 1) {
                Log::warning('Locale "'.$locale.'" was not found! Falling back to similar locale "'.$found_locales[0].'"');
                $locale = $found_locales[0];
            }
            else {
                throw new NotFoundResourceException("Locale '".$locale."' was not found in available locales");
            }
        }

        return $locale;
    }

    /**
     * @param $locale
     * @param $group
     * @return array|mixed
     */
    public function getAllTranslations($locale, $group) {
        if(!isset($this->aHandler[$locale])) {
            $this->aHandler[$locale] = $this->createHandler($locale);
        }

        return $this->aHandler[$locale]->getAllTranslations($group);
    }
}