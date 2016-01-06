<?php

// Broker
Route::group(['domain' => 'broker.payword.app', 'namespace' => 'Broker'], function () {
    Route::group(['middleware' => ['web']], function () {
        Route::get('/', 'HomeController@index');
        Route::auth();
    });

    Route::group(['middleware' => ['api']], function () {
        Route::get('api/me', 'ApiController@me');
        Route::post('api/register', 'ApiController@register');
        Route::get('api/public_key', 'ApiController@getPublicKey');
    });
});

// Vendor
Route::group(['domain' => 'vendor.payword.app', 'namespace' => 'Vendor'], function () {
    Route::get('/', function () { return 'Nothing here.'; });

    Route::group(['middleware' => ['api']], function () {
        Route::get('books', 'BooksController@index');
        Route::get('books/{book}', 'BooksController@show');
    });
});
