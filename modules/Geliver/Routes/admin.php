<?php

use Illuminate\Support\Facades\Route;

Route::post('geliver/orders/{order}/send', [
    'as' => 'admin.geliver.orders.send',
    'uses' => 'OrderGeliverController@send',
    'middleware' => 'can:admin.orders.edit',
]);

Route::get('settings/geliver', [
    'as' => 'admin.settings.geliver',
    'middleware' => 'can:admin.settings.edit',
    'uses' => 'GeliverSettingController@edit',
]);

Route::put('settings/geliver', [
    'as' => 'admin.settings.geliver.update',
    'middleware' => 'can:admin.settings.edit',
    'uses' => 'GeliverSettingController@update',
]);
