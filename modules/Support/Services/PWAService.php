<?php

namespace Modules\Support\Services;

use Modules\Media\Entities\File;
use Illuminate\Support\Facades\File as FileSystem;

class PWAService
{
    public function generateIcons(File $iconFile)
    {
        $output_path = public_path('pwa/icons');

        FileSystem::isDirectory($output_path) or FileSystem::makeDirectory($output_path, 0777, true, true);
        FileSystem::cleanDirectory($output_path);

        $ratios = [48, 72, 96, 128, 144, 152, 192, 384, 512];
        $image_data = getimagesize($iconFile->path);
        $orig_width = $image_data[0];
        $orig_height = $image_data[1];
        $media_type = $image_data['mime'];

        $orig = match ($media_type) {
            'image/jpeg' => imagecreatefromjpeg($iconFile->path),
            'image/png' => imagecreatefrompng($iconFile->path)
        };

        foreach ($ratios as $ratio) {
            $new = imagecreatetruecolor($ratio, $ratio);
            imagecopyresampled(
                $new,
                $orig,
                0,
                0,
                0,
                0,
                $ratio,
                $ratio,
                $orig_width,
                $orig_height
            );

            imagepng($new, $output_path . '/' . $ratio . 'x' . $ratio . '.png');
        }
    }


    public function ensureDefaultIcons()
    {
        $output_path = public_path('pwa/icons');
        FileSystem::isDirectory($output_path) or FileSystem::makeDirectory($output_path, 0777, true, true);

        $map = [
            48 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-48x48.png'),
            72 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-72x72.png'),
            96 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-96x96.png'),
            128 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-128x128.png'),
            144 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-144x144.png'),
            152 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-152x152.png'),
            192 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-192x192.png'),
            384 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-384x384.png'),
            512 => base_path('vendor/silviolleite/laravelpwa/assets/images/icons/icon-512x512.png'),
        ];

        foreach ($map as $size => $src) {
            $dest = $output_path . '/' . $size . 'x' . $size . '.png';
            if (!FileSystem::exists($dest) && FileSystem::exists($src)) {
                FileSystem::copy($src, $dest);
            }
        }
    }


    public function updatePWAVersionInServiceWorkerJs()
    {
        $path = public_path('serviceworker.js');
        $serviceWorkerJs = @file_get_contents($path);

        if (strpos($serviceWorkerJs, 'const pwaVersion')) {
            $serviceWorkerJs = preg_replace('/^const pwaVersion.*\n$/m', 'const pwaVersion = ' . time() . ";\n", $serviceWorkerJs);
        } else {
            $serviceWorkerJs .= "\n" . 'const pwaVersion = ' . time() . ';';
        }

        @file_put_contents($path, $serviceWorkerJs);
    }
}
