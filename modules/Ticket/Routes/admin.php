<?php

use Illuminate\Support\Facades\Route;

Route::get('tickets', [
    'as' => 'admin.tickets.index',
    'uses' => 'TicketController@index',
    'middleware' => 'can:admin.orders.index',
]);

Route::get('tickets/{id}', [
    'as' => 'admin.tickets.show',
    'uses' => 'TicketController@show',
    'middleware' => 'can:admin.orders.index',
]);

Route::get('tickets/{id}/edit', [
    'as' => 'admin.tickets.edit',
    'uses' => 'TicketController@edit',
    'middleware' => 'can:admin.orders.index',
]);

Route::post('tickets/{id}/messages', [
    'as' => 'admin.tickets.messages.store',
    'uses' => 'TicketController@storeMessage',
    'middleware' => 'can:admin.orders.index',
]);

Route::post('tickets/{id}/close', [
    'as' => 'admin.tickets.close',
    'uses' => 'TicketController@close',
    'middleware' => 'can:admin.orders.index',
]);

Route::get('tickets/index/table', [
    'as' => 'admin.tickets.table',
    'uses' => 'TicketController@table',
    'middleware' => 'can:admin.orders.index',
]);
