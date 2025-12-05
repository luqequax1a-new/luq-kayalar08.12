<?php

use Illuminate\Support\Facades\Route;

Route::get('seo/bulk-meta', [
    'as' => 'admin.seo.bulk_meta.index',
    'uses' => 'BulkMetaController@index',
    'middleware' => 'can:admin.products.index',
]);

Route::post('seo/bulk-meta/execute', [
    'as' => 'admin.seo.bulk_meta.execute',
    'uses' => 'BulkMetaController@execute',
    'middleware' => 'can:admin.products.index',
]);

Route::get('seo/categories/tree', [
    'as' => 'admin.seo.categories.tree',
    'uses' => 'CategoryProxyController@index',
    'middleware' => 'can:admin.products.index',
]);
