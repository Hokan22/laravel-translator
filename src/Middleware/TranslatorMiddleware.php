<?php


namespace Hokan22\LaravelTranslator\Middleware;

use Hokan22\LaravelTranslator\TranslatorFacade;
use Closure;
use Illuminate\Support\Facades\Session;

class TranslatorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('locale') || auth()->check()) {
            $locale = Session::has('locale') ? session()->get('locale') : auth()->user()->language;

            $locale = TranslatorFacade::validateLocale($locale);

            if(Session::has('locale') == false){
                Session::put('locale', $locale);
                Session::save();
            }

            app()->setLocale($locale);
        }
        else {
            // TODO: Validate Browser locale string (https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4)

            Session::put('locale', TranslatorFacade::getConfigValue('default_locale'));
            Session::save();
        }

        TranslatorFacade::setLocale(session()->get('locale'));

        return $next($request);
    }

}