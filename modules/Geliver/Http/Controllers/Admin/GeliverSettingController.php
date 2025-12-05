<?php

namespace Modules\Geliver\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class GeliverSettingController
{
    public function edit()
    {
        return view('geliver::admin.settings', [
            'settings' => setting()->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->only([
            'geliver_enabled',
            'geliver_api_token',
            'geliver_sender_address_id',
            'geliver_default_length',
            'geliver_default_width',
            'geliver_default_height',
            'geliver_default_weight',
            'geliver_test_mode',
            'geliver_webhook_secret',
        ]);

        setting($data);

        return redirect()->route('admin.settings.geliver')
            ->withSuccess(trans('setting::messages.settings_updated'));
    }
}

