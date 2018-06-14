<?php

/**
 * Provider
 */
namespace Hokan22\LaravelTranslator\Provider;


use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Class TranslatorBladeProvider
 *
 * @package  Hokan22\LaravelTranslator\Provider
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 */
class TranslatorBladeProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot() {
        Blade::directive('translate', function($expression) {

                $expression = $this->stripParentheses($expression);

                // Call the TranslatorFacade to translate the string
                return "<?php echo Hokan22\\LaravelTranslator\\TranslatorFacade::translate({$expression}); ?>";
            }
        );

        Blade::directive('t', function($expression) {

                $expression = $this->stripParentheses($expression);

                // Call the TranslatorFacade to translate the string
                return "<?php echo Hokan22\\LaravelTranslator\\TranslatorFacade::translate({$expression}); ?>";
            }
        );
    }

    /**
     * Strip the parentheses from the given expression.
     *
     * @param string $expression
     * @return string
     */
    public function stripParentheses($expression) {
        if (Str::startsWith($expression, '(')) {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }
}
