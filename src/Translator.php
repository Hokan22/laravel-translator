<?php

/**
 * Translator
 */
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
 *
 * @package  Hokan22\LaravelTranslator
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 */
class Translator
{
    /** @var array|HandlerInterface[] $aHandler Class to Handle the Translation defined in config */
    protected $aHandler = [];
    /** @var string $locale The locale to translate to  */
    protected $locale = '';
    /** @var string $group The translation group */
    protected $group = 'default';
    /** @var string $configName The name of the config file */
    protected $configName = 'translator';
    /** @var array $config Config cache */
    protected $config;

    /**
     * Translator constructor.
     *
     * @param string $locale The locale to translate to
     * @throws \Exception
     */
    public function __construct($locale = '')
    {
        $this->config = Config::get($this->configName);

        $this->setLocale($locale);
    }

    /**
     * Return the config value for given key
     *
     * @param string $key Key for the config value to get
     * @return string|array Config value for $key
     */
    public function getConfigValue($key)
    {
        return $this->config[$key];
    }

    /**
     * Actual translate function
     * $parameter and $locale are optional
     *
     * @param string $identifier The identifier of the translation
     * @param array|null $parameters The parameters to inject into the translation
     * @param string $locale The locale to which to translate to overrides the class location for one translation
     * @throws \Exception
     * @return string Returns the translation with replaced parameters
     *
     */
    public function translate($identifier, $parameters = null, $locale = null)
    {
        if ($locale !== null) {
            $locale = $this->validateLocale($locale);
        } else {
            $locale = $this->locale;
        }

        if (!isset($this->aHandler[$locale])) {
            $this->aHandler[$locale] = $this->createHandler($locale);
        }

        try {
            $translation = $this->aHandler[$locale]->getTranslation($identifier, $this->group);
        } catch (NotFoundResourceException $exception) {
            // Thrown when the Identifier wasn't found
            $this->log($exception, 'error');

            if ($this->config['listening_enabled'] === true) {
                $this->addMissingIdentifierToDB($identifier, $parameters, 'default');
            }

            return $this->returnMissingTranslation($identifier, $locale);

        } catch (TranslationNotFoundException $exception) {
            // Thrown when no translation for the locale was found
            $this->log($exception, 'error');

            return $this->returnMissingTranslation($identifier, $locale);
        }

        if (is_array($parameters)) {
            $translation = $this->replaceParameter($translation, $parameters);
        }

        if (session('translation_live_mode')) {
            $id = $this->aHandler[$locale]->getDatabaseID($identifier);
            $translation = $this->addLiveModeLink($translation, $id);
        }

        // Return the translation
        return $translation;
    }

    /**
     * Inject a Link to the edit page into the translation
     *
     * @param string $translation
     * @param integer $id
     * @return string
     */
    public function addLiveModeLink($translation, $id) {

        $route = route('translator.admin.edit', ['id' => $id]);

        $inject = "<translation-anchor onclick='window.open(\"$route\", \"_blank\")' style='position: absolute; z-index: 999; cursor: pointer;'>&#9875;</translation-anchor>";

        $translation = "$translation $inject";

        return $translation;
    }

     /**
      * Sets the Handler
      *
      * @param $locale
      * @return HandlerInterface
      */
    protected function createHandler($locale)
    {
        $handler_class = $this->config['handler'];

        if (session('translation_live_mode')) {
            $handler_class = DatabaseHandler::class;
        }

        $oHandler = new $handler_class($locale);

        return $oHandler;
    }

    /**
     * Set the locale to use in translations
     *
     * @param string $locale The locale to use
     */
    public function setLocale($locale)
    {
        $locale = $this->validateLocale($locale);

        if (!isset($this->aHandler[$locale])) {
            $this->aHandler[$locale] = $this->createHandler($locale);
        }

        $this->locale = $locale;
    }

    /**
     * Add the missing identifier to the texts table in the database
     *
     * @param string $identifier The identifier to add to the db
     * @param array $parameters The parameters available for the translation
     * @param string $group The group to put the identifier in
     */
    public function addMissingIdentifierToDB($identifier, $parameters, $group)
    {
        if (!$this->hasIdentifier($identifier)) {

            $keys = [];
            if (is_array($parameters)) {
                foreach($parameters as $key => $value) {
                    $keys[] = $key;
                }
            }

            TranslationIdentifier::create(
                [
                    "identifier"    => $identifier,
                    "parameters"    => $keys,
                    "group"         => isset($group) ? $group : 'default',
                    "page_name"     => app()->runningInConsole() ? '' : substr(request()->getRequestUri(), 1),
                ]
            );

            if (isset($this->aHandler[$this->locale])) {
                // When using file Cache, adding the Identifier to the Database will not add it to file Cache!
                $this->aHandler[$this->locale]->refreshCache();
            }
            $this->log('The translation string "'.$identifier.'" will be written to the Database', 'notice');
        } else {
            $this->log('The translation string "'.$identifier.'" is already in the Database!', 'warning');
        }
    }

    /**
     * Check if the identifier exists in the database
     *
     * @param string $identifier The identifier to check
     * @return boolean Returns true if the identifier was found
     */
    public function hasIdentifier($identifier)
    {
        return TranslationIdentifier::where('identifier', $identifier)->count() > 0;
    }

    /**
     * Replace the parameters in the translation
     *
     * @param string $translation The translation with the parameter tags
     * @param array $parameters The parameters which to inject in the translation
     * @return string Returns the translation which its parameters replaced
     *
     * @todo Make Prefix and Suffix configurable
     */
    protected function replaceParameter($translation, $parameters)
    {
        foreach ($parameters as $key => $parameter) {
            // If the string (e.g "{name}") is not specified within the "parameters" array it won't be replaced!
            $translation = str_replace("{".$key."}", $parameter, $translation);
        }
        return $translation;
    }

    /**
     * Return the translation identifier and the locale
     *
     * @param string $identifier The identifier which is missing
     * @param string $locale The locale of which the translation is missing
     * @throws \Exception
     * @return string The string to display instead of the translation
     */
    protected function returnMissingTranslation($identifier, $locale)
    {
        if (config('app.env') !== 'production') {
            return '&lt;'.$identifier.':'.$locale.'&gt;';
        }
        return $identifier;
    }

    /**
     * Checks if the given locale is in the available_locales array.
     * If not try to guess it or fall back to the default locale
     *
     * @param string $locale The locale to validate
     * @return string Returns the validated Locale
     */
    public function validateLocale($locale)
    {
        $avail_locales      = $this->config['available_locales'];
        $default_locale     = $this->config['default_locale'];

        if ($this->locale == $locale && $this->locale !== '') {
            return $locale;
        }

        if ($locale == null) {
            if (session()->get('locale') != '') {
                $locale = session()->get('locale');
            } else {
                return $default_locale;
            }
        }

        if (!in_array($locale, $avail_locales)) {
            $locale = $this->guessLocale($locale);
        }

        return $locale;
    }

    /**
     * Tries to match the locale to an available Locale
     * Else returns the default locale
     *
     * @param string $locale The locale to match
     * @throws NotFoundResourceException
     * @return string Returns the guessed Locale
     */
    private function guessLocale($locale)
    {
        $avail_locales      = $this->config['available_locales'];
        $default_locale     = $this->config['default_locale'];

        $found_locales = [];

        foreach ($avail_locales as $avail_locale) {
            if (strpos($avail_locale, $locale) !== false){
                $found_locales[] = $avail_locale;
            }
        }

        if (in_array($default_locale, $found_locales) || count($found_locales) == 0){
            $message = 'Locale "'.$locale.'" was not found! Falling back to default locale "'.$default_locale.'"';
            $locale = $default_locale;

        } else {
            $message = 'Locale "'.$locale.'" was not found! Falling back to similar locale "'.$found_locales[0].'"';
            $locale = $found_locales[0];
        }

        if ($message !== '') $this->log($message, 'warning');

        return $locale;
    }

    /**
     * Returns all translation for the the given locale of the given group
     *
     * @param string $locale The locale of the translations to get
     * @param string $group The group of the translations to get
     * @return array|mixed Returns an array of all translation in the $locale from group $group
     */
    public function getAllTranslations($locale, $group)
    {
        if (!isset($this->aHandler[$locale])) {
            $this->aHandler[$locale] = $this->createHandler($locale);
        }

        return $this->aHandler[$locale]->getAllTranslations($group);
    }

    /**
     * Log the given exception or string $exception as type $log_type when config log_level is set to debug
     *
     * @param \Exception|string $exception The Exception to log
     * @param string $log_type The type of the log to write in the log file
     */
    protected function log($exception, $log_type = 'notice')
    {
        if ($this->config['log_level'] !== 'debug') {
            return;
        }

        switch ($log_type) {
            case 'error':
                Log::error($exception);
                break;

            case 'warning':
                Log::warning($exception);
                break;

            case 'notice':
            default :
                Log::notice($exception);
                break;
        }
    }
}