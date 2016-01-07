<?php

// Broker
Route::group(['domain' => 'broker.payword.app', 'namespace' => 'Broker'], function () {
    Route::group(['middleware' => ['web']], function () {
        Route::get('/', 'HomeController@index');
        Route::get('settings', 'HomeController@getSettings');
        Route::post('settings', 'HomeController@postSettings');
        Route::auth();
    });

    Route::group(['middleware' => ['api']], function () {
        Route::get('api/me', 'ApiController@me');
        Route::post('api/register', 'ApiController@register');
        Route::get('api/public_key', 'ApiController@getPublicKey');
        Route::post('api/block_money', 'ApiController@blockMoney');
    });
});

// Vendor
Route::group(['domain' => 'vendor.payword.app', 'namespace' => 'Vendor'], function () {
    Route::get('/', function () { return 'Nothing here.'; });

    Route::group(['middleware' => ['api']], function () {
        Route::get('api/books', 'BooksController@index');
        Route::get('api/books/{book}', 'BooksController@show');
        Route::post('api/books/{book}', 'BooksController@verifyCommits');
    });
});
