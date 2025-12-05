<?php

use Illuminate\Support\Facades\Route;

Route::post('webhook/geliver/shipment-status', [
    'as' => 'webhook.geliver.shipment_status',
    'uses' => 'WebhookController@shipmentStatus',
])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('{locale}/webhook/geliver/shipment-status', [
    'as' => 'webhook.geliver.shipment_status.localized',
    'uses' => 'WebhookController@shipmentStatus',
])->where('locale', '[a-zA-Z]{2}')
  ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
