<?php

use Illuminate\Support\Facades\Route;

Route::get('products', [
    'as' => 'admin.products.index',
    'uses' => 'ProductController@index',
    'middleware' => 'can:admin.products.index',
]);

Route::get(
    'products/create',
    [
        'as' => 'admin.products.create',
        'uses' => 'ProductController@create',
        'middleware' => 'can:admin.products.create',
    ]
);

Route::post('products', [
    'as' => 'admin.products.store',
    'uses' => 'ProductController@store',
    'middleware' => 'can:admin.products.create',
]);

Route::get('products/{id}/edit', [
    'as' => 'admin.products.edit',
    'uses' => 'ProductController@edit',
    'middleware' => 'can:admin.products.edit',
]);

Route::put('products/{id}', [
    'as' => 'admin.products.update',
    'uses' => 'ProductController@update',
    'middleware' => 'can:admin.products.edit',
]);

Route::patch('products/{id}/status', [
    'as' => 'admin.products.status',
    'uses' => 'ProductController@status',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/{id}/duplicate', [
    'as' => 'admin.products.duplicate',
    'uses' => 'ProductController@duplicate',
    'middleware' => 'can:admin.products.edit',
]);

Route::delete('products/{ids}', [
    'as' => 'admin.products.destroy',
    'uses' => 'ProductController@destroy',
    'middleware' => 'can:admin.products.destroy',
]);

Route::get('products/index/table', [
    'as' => 'admin.products.table',
    'uses' => 'ProductController@table',
    'middleware' => 'can:admin.products.index',
]);

Route::get('google-taxonomy', [
    'as' => 'admin.google_taxonomy.index',
    'uses' => 'TaxonomyController@index',
    'middleware' => 'can:admin.products.index',
]);
Route::get('products/{id}/inventory', [
    'as' => 'admin.products.inventory.show',
    'uses' => 'ProductController@inventory',
    'middleware' => 'can:admin.products.edit',
]);

Route::patch('products/{id}/inventory', [
    'as' => 'admin.products.inventory.update',
    'uses' => 'ProductController@updateInventory',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('redirects', [
    'as' => 'admin.redirects.index',
    'uses' => 'RedirectController@index',
    'middleware' => 'can:admin.products.index',
]);

Route::get('redirects/index/table', [
    'as' => 'admin.redirects.table',
    'uses' => 'RedirectController@table',
    'middleware' => 'can:admin.products.index',
]);

Route::get('redirects/create', [
    'as' => 'admin.redirects.create',
    'uses' => 'RedirectController@create',
    'middleware' => 'can:admin.products.create',
]);

Route::post('redirects', [
    'as' => 'admin.redirects.store',
    'uses' => 'RedirectController@store',
    'middleware' => 'can:admin.products.create',
]);

Route::get('redirects/{id}/edit', [
    'as' => 'admin.redirects.edit',
    'uses' => 'RedirectController@edit',
    'middleware' => 'can:admin.products.edit',
]);

Route::put('redirects/{id}', [
    'as' => 'admin.redirects.update',
    'uses' => 'RedirectController@update',
    'middleware' => 'can:admin.products.edit',
]);

Route::delete('redirects/{ids}', [
    'as' => 'admin.redirects.destroy',
    'uses' => 'RedirectController@destroy',
    'middleware' => 'can:admin.products.destroy',
]);

Route::patch('redirects/{id}/status', [
    'as' => 'admin.redirects.status',
    'uses' => 'RedirectController@status',
    'middleware' => 'can:admin.products.edit',
]);
