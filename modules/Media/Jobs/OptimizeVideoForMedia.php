<?php

namespace Modules\Media\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File as MediaFile;
use Symfony\Component\Process\Process;

class OptimizeVideoForMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $fileId;

    public function __construct(int $fileId)
    {
        $this->fileId = $fileId;
    }

    public function handle(): void
    {
        $file = MediaFile::find($this->fileId);
        if (!$file) return;

        $mime = strtolower((string) $file->mime);
        if (strpos($mime, 'video/') !== 0) return;

        $disk = $file->disk ?: config('filesystems.default');
        $inputRelative = $file->getRawOriginal('path');
        if (empty($inputRelative)) return;

        $inputAbs = Storage::disk($disk)->path($inputRelative);

        $dir = dirname($inputRelative);
        $name = pathinfo($inputRelative, PATHINFO_FILENAME);
        $outputRelative = $dir . '/' . $name . '-opt.mp4';
        $outputAbs = Storage::disk($disk)->path($outputRelative);

        // Ensure output directory exists
        @mkdir(dirname($outputAbs), 0775, true);

        // ffmpeg command
        $cmd = [
            'ffmpeg', '-y',
            '-i', $inputAbs,
            '-vcodec', 'libx264',
            '-profile:v', 'high',
            '-preset', 'slow',
            '-crf', '24',
            '-movflags', '+faststart',
            '-vf', 'scale=trunc(iw/2)*2:trunc(ih/2)*2',
            '-acodec', 'aac', '-b:a', '128k',
            $outputAbs,
        ];

        try {
            $process = new Process($cmd);
            $process->setTimeout(300); // 5 minutes
            $process->run();

            if (!$process->isSuccessful()) {
                // Fallback: try more conservative settings
                $cmdFallback = [
                    'ffmpeg', '-y', '-i', $inputAbs,
                    '-vcodec', 'libx264', '-crf', '26', '-movflags', '+faststart',
                    '-vf', 'scale=trunc(iw/2)*2:trunc(ih/2)*2',
                    '-acodec', 'aac', '-b:a', '128k',
                    $outputAbs,
                ];
                $process = new Process($cmdFallback);
                $process->setTimeout(300);
                $process->run();
            }

            // If succeeded, update record and remove original
            if (file_exists($outputAbs) && filesize($outputAbs) > 0) {
                $file->path = $outputRelative;
                $file->extension = 'mp4';
                $file->mime = 'video/mp4';
                $file->size = @filesize($outputAbs) ?: $file->size;
                $file->save();

                // remove original
                try { Storage::disk($disk)->delete($inputRelative); } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            // Silently fail; keep original
        }

        try {
            $currentRel = $file->getRawOriginal('path');
            if (!$currentRel) return;
            $currentAbs = Storage::disk($disk)->path($currentRel);
            $curDir = dirname($currentRel);
            $curName = pathinfo($currentRel, PATHINFO_FILENAME);
            $posterWebpRel = $curDir . '/' . $curName . '-400w.webp';
            $posterWebpAbs = Storage::disk($disk)->path($posterWebpRel);
            @mkdir(dirname($posterWebpAbs), 0775, true);
            if (!file_exists($posterWebpAbs)) {
                $posterCmd = ['ffmpeg','-y','-ss','5','-i',$currentAbs,'-vframes','1','-vf','scale=400:-2',$posterWebpAbs];
                $p = new Process($posterCmd);
                $p->setTimeout(60);
                $p->run();
            }
            if (!file_exists($posterWebpAbs) || filesize($posterWebpAbs) <= 0) {
                $posterJpgRel = $curDir . '/' . $curName . '-400w.jpg';
                $posterJpgAbs = Storage::disk($disk)->path($posterJpgRel);
                @mkdir(dirname($posterJpgAbs), 0775, true);
                if (!file_exists($posterJpgAbs)) {
                    $posterCmd2 = ['ffmpeg','-y','-ss','5','-i',$currentAbs,'-vframes','1','-vf','scale=400:-2',$posterJpgAbs];
                    $p2 = new Process($posterCmd2);
                    $p2->setTimeout(60);
                    $p2->run();
                }
            }
        } catch (\Throwable $e) {}
    }
}
