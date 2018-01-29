<?php

/**
 * PHP version 5.6
 *
 * Translator
 *
 * @category Translator
 * @package  Hokan22\LaravelTranslator
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/Hokan22/laravel-translator
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
 * @category Translator
 * @package  Hokan22\LaravelTranslator
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/Hokan22/laravel-translator
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
     *
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
     *
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
     *
     * @throws \Exception
     *
     * @return string Returns the translation with replaced parameters
     *
     * @todo Make function Parameters interchangeable
     */
    public function translate($identifier, $parameters = null, $locale = null)
    {
        // Validate the locale given as parameter or take the saved locale
        if ($locale !== null) {
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

        } catch (NotFoundResourceException $exception) {
            // Thrown when the Identifier wasn't found
            // Log exception as error in Laravel log
            $this->log($exception, 'error');

            // Listener: When app is not in production and listening is enabled
            // add any missing translation identifier to the database
            if ($this->config['listening_enabled'] === true) {
                $this->addMissingIdentifier($identifier, $parameters, 'default');
            }

            return $this->returnMissingTranslation($identifier, $locale);

        } catch (TranslationNotFoundException $exception) {
            // Thrown when no translation for the locale was found
            // Log exception as error in Laravel log
            $this->log($exception, 'error');

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
     * Creates the Handler for the given locale
     *
     * @param string $locale The locale for which to create a handler
     *
     * @return HandlerInterface
     */
    protected function createHandler($locale)
    {
        // Get the Handler class from config file
        $handler_class = $this->config['handler'];
        // Define message as empty for later check
        $oHandler = null;

        // Try to create new Instance of Handler and return it
        // If creating the Handler fails or it does not implement HandlerInterface the DatabaseHandler will be used
        try {
            $oHandler = new $handler_class($locale);
            if (!is_a($handler_class, 'Hokan22\LaravelTranslator\Handler\HandlerInterface', true)) {
                throw new \Exception($handler_class . ' does not implement HandlerInterface!');
            }
        } catch (\Exception $exception) {
            // Log error and fallback procedure
            $this->log($exception, 'error');
            $this->log('Falling back to DatabaseHandler', 'warning');

            // Fallback to Database Handler
            $oHandler = new DatabaseHandler($locale);
        }
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
    public function addMissingIdentifier($identifier, $parameters, $group)
    {
        if (!$this->hasIdentifier($identifier)) {

            // Save only the keys from the parameter array
            $keys = [];
            if (is_array($parameters)) {
                foreach($parameters as $key => $value) {
                    $keys[] = $key;
                }
            }
            // Create new TranslationIdentifier with parameters and current url
            TranslationIdentifier::create(
                [
                    "identifier"    => $identifier,
                    "parameters"    => $keys,
                    "group"         => isset($group) ? $group : 'default',
                    "page_name"     => app()->runningInConsole() ? '' : substr(request()->getRequestUri(), 1),
                ]
            );

            if (isset($this->aHandler[$this->locale])) {
                // refresh the Cache for the handler
                // When using file Cache, adding the Identifier to the Database will not add it to file Cache!
                $this->aHandler[$this->locale]->refreshCache();
            }
            // Print notice about creation to laravel log
            $this->log('The translation string "'.$identifier.'" will be written to the Database', 'notice');
        } else {
            $this->log('The translation string "'.$identifier.'" is already in the Database!', 'warning');
        }
    }

    /**
     * Check if the identifier exists in the database
     *
     * @param string $identifier The identifier to check
     *
     * @return boolean Returns true if the identifier was found
     */
    public function hasIdentifier($identifier)
    {
        // Returns true if at least one identifier was found
        return TranslationIdentifier::where('identifier', $identifier)->count() > 0;
    }

    /**
     * Replace the parameters in the translation
     *
     * @param string $translation The translation with the parameter tags
     * @param array $parameters The parameters which to inject in the translation
     *
     * @return string Returns the translation which its parameters replaced
     *
     * @todo Make Prefix and Suffix configurable
     */
    protected function replaceParameter($translation, $parameters)
    {
        // Go through each specified Parameter and replace its placeholder "{$key}"
        foreach ($parameters as $key => $parameter) {
            // If the string (e.g "{name}") is not specified within the "parameters" array it won't be replaced!
            $translation = str_replace("{".$key."}", $parameter, $translation);
        }
        return $translation;
    }

    /**
     * Instead of the translation echo back 'Missing Translation' (when not in production!)
     * and show the translation identifier ($identifier) and the locale
     *
     * @param string $identifier The identifier which is missing
     * @param string $locale The locale of which the translation is missing
     *
     * @throws \Exception
     *
     * @return string The string to display instead of the translation
     */
    protected function returnMissingTranslation($identifier, $locale)
    {
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
     * @param string $locale The locale to validate
     *
     * @throws NotFoundResourceException
     *
     * @return string Returns the validated Locale
     */
    public function validateLocale($locale)
    {
        // Set message for later log warning
        $message = '';

        //Get Locales configs from translator config file
        $avail_locales      = $this->config['available_locales'];
        $default_locale     = $this->config['default_locale'];

        // If locale is already set and not empty it has already been checked
        if ($this->locale == $locale && $this->locale !== '') {
            return $locale;
        }

        // Fallback if empty locale was given (should be handled in middleware)
        if ($locale == null){
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
                $message = 'Locale "'.$locale.'" was not found! Falling back to default locale "'.$default_locale.'"';
                $locale = $default_locale;

                // Check if any Locale containing '$locale' was found previously
            } elseif (count($found_locales, 0) >= 1) {
                $message = 'Locale "'.$locale.'" was not found! Falling back to similar locale "'.$found_locales[0].'"';
                $locale = $found_locales[0];
            } else {
                throw new NotFoundResourceException("Locale '".$locale."' was not found in available locales");
            }
        }

        if ($message !== '') $this->log($message, 'warning');

        return $locale;
    }

    /**
     * Returns all translation in the in $locale from $group
     *
     * @param string $locale The locale of the translations to get
     * @param string $group The group of the translations to get
     *
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
     *
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