<?php

namespace Modules\Media\Services;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class ImageOptimizationService
{
    public function optimizeUploadedFile(UploadedFile $file): ?array
    {
        $mime = strtolower((string) $file->getClientMimeType());
        if (strpos($mime, 'image/') !== 0) {
            return null;
        }

        $config = (array) config('image_optimization.master', []);
        $maxWidth = (int) ($config['max_width'] ?? 1800);
        $maxHeight = (int) ($config['max_height'] ?? 1800);
        $jpegQuality = (int) ($config['jpeg_quality'] ?? 78);
        $pngLevel = (int) ($config['png_compression_level'] ?? 7);
        $convertOpaquePng = (bool) ($config['convert_opaque_png_to_jpeg'] ?? true);
        $fixOrientation = (bool) ($config['fix_orientation'] ?? true);
        $bgColor = $config['jpeg_background_color'] ?? [255, 255, 255];

        if (!is_array($bgColor) || count($bgColor) < 3) {
            $bgColor = [255, 255, 255];
        }

        try {
            $realPath = $file->getRealPath();
            if (!$realPath || !is_file($realPath)) {
                return null;
            }

            $info = @getimagesize($realPath);
            if (!is_array($info) || empty($info[0]) || empty($info[1])) {
                return null;
            }

            $origWidth = (int) $info[0];
            $origHeight = (int) $info[1];
            $origMime = strtolower((string) ($info['mime'] ?? $mime));

            $binary = @file_get_contents($realPath);
            if ($binary === false) {
                return null;
            }

            $img = @imagecreatefromstring($binary);

            if ($img === false && class_exists('\\Imagick')) {
                try {
                    $im = new \Imagick($realPath);
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

            if ($img === false) {
                return null;
            }

            $isJpeg = in_array($origMime, ['image/jpeg', 'image/jpg']);
            $isPng = $origMime === 'image/png';

            if ($fixOrientation && $isJpeg && function_exists('exif_read_data')) {
                $this->fixOrientation($img, $realPath);
            }

            $scale = 1.0;
            if ($origWidth > 0 && $origHeight > 0) {
                $ratioW = $maxWidth > 0 ? $maxWidth / $origWidth : 1.0;
                $ratioH = $maxHeight > 0 ? $maxHeight / $origHeight : 1.0;
                $scale = min($ratioW, $ratioH, 1.0);
            }

            $targetWidth = max(1, (int) round($origWidth * $scale));
            $targetHeight = max(1, (int) round($origHeight * $scale));

            $hasAlpha = false;
            if ($isPng) {
                $transparentIndex = imagecolortransparent($img);
                if ($transparentIndex >= 0) {
                    $hasAlpha = true;
                } else {
                    if (function_exists('imageistruecolor') && !imageistruecolor($img)) {
                        $hasAlpha = true;
                    }
                }
            }

            $outputMime = $origMime;
            $extension = strtolower($file->guessClientExtension() ?? pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

            if ($isPng) {
                if ($hasAlpha || !$convertOpaquePng) {
                    $outputMime = 'image/png';
                    $extension = 'png';
                } else {
                    $outputMime = 'image/jpeg';
                    $extension = 'jpg';
                }
            } elseif ($isJpeg) {
                $outputMime = 'image/jpeg';
                $extension = $extension ?: 'jpg';
            } else {
                imagedestroy($img);
                return null;
            }

            $dst = imagecreatetruecolor($targetWidth, $targetHeight);
            if ($dst === false) {
                imagedestroy($img);
                return null;
            }

            if ($outputMime === 'image/png' && $hasAlpha) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $targetWidth, $targetHeight, $transparent);
            } else {
                $r = (int) ($bgColor[0] ?? 255);
                $g = (int) ($bgColor[1] ?? 255);
                $b = (int) ($bgColor[2] ?? 255);
                $bg = imagecolorallocate($dst, $r, $g, $b);
                imagefilledrectangle($dst, 0, 0, $targetWidth, $targetHeight, $bg);
            }

            if (!imagecopyresampled($dst, $img, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight)) {
                imagedestroy($img);
                imagedestroy($dst);
                return null;
            }

            ob_start();
            $ok = false;
            if ($outputMime === 'image/jpeg') {
                $ok = imagejpeg($dst, null, max(0, min(100, $jpegQuality)));
            } elseif ($outputMime === 'image/png') {
                $level = max(0, min(9, $pngLevel));
                $ok = imagepng($dst, null, $level);
            }
            $data = ob_get_clean();

            imagedestroy($img);
            imagedestroy($dst);

            if (!$ok || !is_string($data) || strlen($data) === 0) {
                return null;
            }

            $tmpBase = tempnam(sys_get_temp_dir(), 'fc_img_');
            if ($tmpBase === false) {
                return null;
            }

            $tmpPath = $tmpBase . '.' . $extension;
            @unlink($tmpBase);

            if (@file_put_contents($tmpPath, $data) === false) {
                @unlink($tmpPath);
                return null;
            }

            $symfonyFile = new SymfonyFile($tmpPath, false);

            return [
                'file' => $symfonyFile,
                'extension' => $extension,
                'mime' => $outputMime,
                'size' => filesize($tmpPath) ?: null,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function fixOrientation($image, string $path): void
    {
        if (!function_exists('exif_read_data')) {
            return;
        }

        try {
            $exif = @exif_read_data($path);
            if (!is_array($exif) || empty($exif['Orientation'])) {
                return;
            }

            $orientation = (int) $exif['Orientation'];
            if ($orientation === 3) {
                $rotated = imagerotate($image, 180, 0);
            } elseif ($orientation === 6) {
                $rotated = imagerotate($image, -90, 0);
            } elseif ($orientation === 8) {
                $rotated = imagerotate($image, 90, 0);
            } else {
                return;
            }

            if ($rotated !== false) {
                imagecopy($image, $rotated, 0, 0, 0, 0, imagesx($rotated), imagesy($rotated));
                imagedestroy($rotated);
            }
        } catch (\Throwable $e) {
        }
    }
}
