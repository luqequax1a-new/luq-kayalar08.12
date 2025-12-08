<?php

use Illuminate\Support\Facades\Route;

Route::get('dynamic-categories', [
    'as' => 'admin.dynamic_categories.index',
    'uses' => '\\Modules\\DynamicCategory\\Http\\Controllers\\Admin\\DynamicCategoryController@index',
    'middleware' => 'can:admin.dynamic_categories.index',
]);

Route::get('dynamic-categories/create', [
    'as' => 'admin.dynamic_categories.create',
    'uses' => '\\Modules\\DynamicCategory\\Http\\Controllers\\Admin\\DynamicCategoryController@create',
    'middleware' => 'can:admin.dynamic_categories.create',
]);

Route::post('dynamic-categories', [
    'as' => 'admin.dynamic_categories.store',
    'uses' => '\\Modules\\DynamicCategory\\Http\\Controllers\\Admin\\DynamicCategoryController@store',
    'middleware' => 'can:admin.dynamic_categories.create',
]);

Route::get('dynamic-categories/{id}/edit', [
    'as' => 'admin.dynamic_categories.edit',
    'uses' => '\\Modules\\DynamicCategory\\Http\\Controllers\\Admin\\DynamicCategoryController@edit',
    'middleware' => 'can:admin.dynamic_categories.edit',
]);

Route::put('dynamic-categories/{id}', [
    'as' => 'admin.dynamic_categories.update',
    'uses' => '\\Modules\\DynamicCategory\\Http\\Controllers\\Admin\\DynamicCategoryController@update',
    'middleware' => 'can:admin.dynamic_categories.edit',
]);

Route::delete('dynamic-categories/{ids?}', [
    'as' => 'admin.dynamic_categories.destroy',
    'uses' => '\\Modules\\DynamicCategory\\Http\\Controllers\\Admin\\DynamicCategoryController@destroy',
    'middleware' => 'can:admin.dynamic_categories.destroy',
]);

Route::get('dynamic-categories/index/table', [
    'as' => 'admin.dynamic_categories.table',
    'uses' => '\\Modules\\DynamicCategory\\Http\\Controllers\\Admin\\DynamicCategoryController@table',
    'middleware' => 'can:admin.dynamic_categories.index',
]);
