<?php

namespace Modules\Support\Http\Controllers;

use Modules\Support\Services\ManifestService;

class ManifestController
{
    public function json(ManifestService $manifestService)
    {
        app(\Modules\Support\Services\PWAService::class)->ensureDefaultIcons();
        return response()->json(
            $manifestService->generate(),
            options: JSON_UNESCAPED_SLASHES
        );
    }


    public function offline()
    {
        return view('support::pwa.offline');
    }
}
