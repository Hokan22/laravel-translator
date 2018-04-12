<?php
/**
 * Routes
 *
 * @package  Hokan22\LaravelTranslator\resources
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 */

if (! Hokan22\LaravelTranslator\TranslatorFacade::getConfigValue('custom_routes')) {
    Route::group(['prefix' => 'translator'], function () {
        Route::get('/test', 'Hokan22\LaravelTranslator\Controllers\TranslatorAdminController@test')->name('translator.test');

        Route::group(['prefix' => 'admin'], function () {
            Route::get('/', 'Hokan22\LaravelTranslator\Controllers\TranslatorAdminController@index')->name('translator.admin');
            Route::post('/', 'Hokan22\LaravelTranslator\Controllers\TranslatorAdminController@postIdentifier')->name('translator.post.admin');
            Route::get('/{id}', 'Hokan22\LaravelTranslator\Controllers\TranslatorAdminController@edit')->where('id', '[0-9]+')->name('translator.admin.edit');
            Route::post('/{id}', 'Hokan22\LaravelTranslator\Controllers\TranslatorAdminController@postEdit')->where('id', '[0-9]+')->name('translator.admin.post.edit');
            Route::get('/livemode/{state}', 'Hokan22\LaravelTranslator\Controllers\TranslatorAdminController@changeLiveMode')->where('state', '[a-z]+')->name('translator.change.live_mode');
        });
    });
}
