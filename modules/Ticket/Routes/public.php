<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('account/messages', 'Account\\TicketController@index')->name('account.tickets.index');
    Route::get('account/messages/create', 'Account\\TicketController@create')->name('account.tickets.create');
    Route::get('account/messages/{id}', 'Account\\TicketController@show')->name('account.tickets.show');
    Route::post('account/messages', 'Account\\TicketController@store')->name('account.tickets.store');
    Route::post('account/messages/{id}', 'Account\\TicketController@storeMessage')->name('account.tickets.messages.store');
});
