<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'can:admin.settings.edit'], function () {
    Route::get('popups', [
        'as' => 'admin.popups.index',
        'uses' => 'PopupController@index',
    ]);
});

Route::group(['middleware' => 'can:admin.settings.edit'], function () {
    Route::get('popups/create', [
        'as' => 'admin.popups.create',
        'uses' => 'PopupController@create',
    ]);

    Route::post('popups', [
        'as' => 'admin.popups.store',
        'uses' => 'PopupController@store',
    ]);
});

Route::group(['middleware' => 'can:admin.settings.edit'], function () {
    Route::get('popups/{id}/edit', [
        'as' => 'admin.popups.edit',
        'uses' => 'PopupController@edit',
    ]);

    Route::put('popups/{id}', [
        'as' => 'admin.popups.update',
        'uses' => 'PopupController@update',
    ]);

    Route::post('popups/{id}/duplicate', [
        'as' => 'admin.popups.duplicate',
        'uses' => 'PopupController@duplicate',
    ]);
});

Route::group(['middleware' => 'can:admin.settings.edit'], function () {
    Route::delete('popups/{id}', [
        'as' => 'admin.popups.destroy',
        'uses' => 'PopupController@destroy',
    ]);
});
