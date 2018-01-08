<?php

if (! Hokan22\LaravelTranslator\TranslatorFacade::getConfigValue('custom_routes')) {

    Route::group(['prefix' => 'translator'], function () {

        Route::get('/test', 'Hokan22\LaravelTranslator\resources\views\TranslatorAdminController@test')->name('translator.test');

        Route::group(['prefix' => 'admin'], function () {

            Route::get('/', 'Hokan22\LaravelTranslator\resources\views\TranslatorAdminController@index')->name('translator.admin');
            Route::post('/', 'Hokan22\LaravelTranslator\resources\views\TranslatorAdminController@postIdentifier')->name('translator.post.admin');

            Route::get('/{id}', 'Hokan22\LaravelTranslator\resources\views\TranslatorAdminController@edit')->where('id', '[0-9]+')->name('translator.admin.edit');
            Route::post('/{id}', 'Hokan22\LaravelTranslator\resources\views\TranslatorAdminController@postEdit')->where('id', '[0-9]+')->name('translator.admin.post.edit');

        });
    });
}
