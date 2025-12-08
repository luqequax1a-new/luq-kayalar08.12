<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'product-feeds',
    'middleware' => 'can:admin.settings.edit',
], function () {
    Route::get('settings', [
        'as' => 'admin.product_feeds.settings.index',
        'uses' => 'ProductFeedSettingsController@index',
    ]);

    Route::post('settings', [
        'as' => 'admin.product_feeds.settings.update',
        'uses' => 'ProductFeedSettingsController@update',
    ]);

    Route::post('cache/refresh/{channel}', [
        'as' => 'admin.product_feeds.cache.refresh',
        'uses' => 'FeedCacheController@refresh',
    ]);

    Route::post('cache/regenerate-token', [
        'as' => 'admin.product_feeds.cache.regenerate_token',
        'uses' => 'FeedCacheController@regenerateToken',
    ]);
});

