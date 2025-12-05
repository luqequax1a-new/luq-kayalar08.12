<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'DashboardController@index')->name('admin.dashboard.index');

Route::get('/sales-analytics', [
    'as' => 'admin.sales_analytics.index',
    'uses' => 'SalesAnalyticsController@index',
    'middleware' => 'can:admin.orders.index',
]);

Route::resource('tag-badges', 'TagBadgeController')
    ->except(['show'])
    ->names('admin.tag_badges');
