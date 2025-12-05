<?php

namespace Modules\Media\Services;

use Modules\Media\Entities\File as MediaFile;
use Illuminate\Support\Facades\Storage;

class ResponsiveImageGenerator
{
    protected array $presets = [
        'grid' => [400],
        'detail' => [1000],
    ];

    protected array $thumbs = [80];

    public function generateVariants(MediaFile $file): void
    {
        if (!$file->isImage()) return;

        $raw = $file->getRawOriginal('path');
        $disk = $file->disk;
        $source = $file->realPath();
        if (!$source || !is_file($source)) return;

        $binary = @file_get_contents($source);
        $img = ($binary !== false) ? @imagecreatefromstring($binary) : false;

        if ($img === false && class_exists('\\Imagick')) {
            try {
                $im = new \Imagick($source);
                $im->setImageFormat('png');
                $pngData = $im->getImageBlob();
                $im->clear();
                $im->destroy();
                if (is_string($pngData) && strlen($pngData) > 0) {
                    $img = @imagecreatefromstring($pngData);
                }
            } catch (\Throwable $e) {
                $img = false;
            }
        }

        if ($img === false) return;

        $ext = strtolower($file->extension ?: pathinfo($raw, PATHINFO_EXTENSION));

        foreach ($this->presets as $sizes) {
            foreach ($sizes as $w) {
                $this->writeVariant($disk, $raw, $img, $ext, $w, null);
                $this->writeVariant($disk, $raw, $img, $ext, $w, 'webp');
                $this->writeVariant($disk, $raw, $img, $ext, $w, 'avif');
            }
        }

        foreach ($this->thumbs as $tw) {
            $this->writeVariant($disk, $raw, $img, $ext, $tw, null);
            $this->writeVariant($disk, $raw, $img, $ext, $tw, 'webp');
            $this->writeVariant($disk, $raw, $img, $ext, $tw, 'avif');
        }

        imagedestroy($img);
    }

    protected function writeVariant(string $disk, string $rawPath, $img, string $originalExt, int $width, ?string $format): void
    {
        $targetRel = $this->buildVariantRelativePath($rawPath, $width, $format ?? $originalExt);
        if (Storage::disk($disk)->exists($targetRel)) return;

        $scaled = imagescale($img, $width);
        if ($scaled === false) return;

        ob_start();
        $ok = false;
        $fmt = strtolower($format ?? $originalExt);

        if (in_array($fmt, ['jpg', 'jpeg'])) {
            $ok = imagejpeg($scaled, null, 88);
            $data = ob_get_clean();
        } elseif ($fmt === 'png') {
            $ok = imagepng($scaled, null, 6);
            $data = ob_get_clean();
        } elseif ($fmt === 'webp') {
            if (function_exists('imagewebp')) {
                $ok = imagewebp($scaled, null, 85);
                $data = ob_get_clean();
            } else {
                $data = $this->encodeWithImagick($img, $width, 'webp', 85);
                $ok = is_string($data) && strlen($data) > 0;
                ob_end_clean();
            }
        } elseif ($fmt === 'avif') {
            if (function_exists('imageavif')) {
                $ok = imageavif($scaled, null, 85);
                $data = ob_get_clean();
            } else {
                $data = $this->encodeWithImagick($img, $width, 'avif', 85);
                $ok = is_string($data) && strlen($data) > 0;
                ob_end_clean();
            }
        } else {
            $ok = imagejpeg($scaled, null, 88);
            $data = ob_get_clean();
        }

        imagedestroy($scaled);

        if ($ok && is_string($data) && strlen($data) > 0) {
            Storage::disk($disk)->put($targetRel, $data);
        }
    }

    protected function buildVariantRelativePath(string $rawPath, int $width, string $format): string
    {
        $dir = trim(dirname($rawPath), '/');
        $name = pathinfo($rawPath, PATHINFO_FILENAME);
        return ($dir ? $dir.'/' : '').$name.'-'.$width.'w'.'.'.strtolower($format);
    }

    protected function encodeWithImagick($gdImage, int $width, string $format, int $quality): ?string
    {
        if (!class_exists('\Imagick')) return null;
        $tmp = tempnam(sys_get_temp_dir(), 'img');
        if (!$tmp) return null;
        ob_start();
        imagepng($gdImage, null, 0);
        $pngData = ob_get_clean();
        if (!is_string($pngData) || strlen($pngData) === 0) {
            @unlink($tmp);
            return null;
        }
        file_put_contents($tmp, $pngData);
        try {
            $imagick = new \Imagick($tmp);
            $imagick->setImageFormat(strtolower($format));
            if (method_exists($imagick, 'resizeImage')) {
                $imagick->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
            }
            if (strtolower($format) === 'webp') {
                $imagick->setImageCompressionQuality($quality);
            }
            if (strtolower($format) === 'avif') {
                if (method_exists($imagick, 'setOption')) {
                    $imagick->setOption('avif:quality', (string) $quality);
                }
            }
            $out = $imagick->getImagesBlob();
            $imagick->clear();
            $imagick->destroy();
            @unlink($tmp);
            return $out;
        } catch (\Throwable $e) {
            @unlink($tmp);
            return null;
        }
    }
}
