<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageStorage
{
    private const MAX_BYTES = 2_097_152;
    private const MAX_DIMENSIONS = [2400, 2000, 1600, 1280, 1024];
    private const QUALITIES = [88, 82, 76, 70, 64, 58];

    public static function store(UploadedFile $file, string $directory): string
    {
        if ($file->getSize() <= self::MAX_BYTES) {
            return $file->store($directory, 'public');
        }

        if (! self::canCompress()) {
            throw ValidationException::withMessages([
                'photos' => 'Ukuran foto maksimal 2MB. Server belum mendukung kompresi gambar otomatis.',
                'photo' => 'Ukuran foto maksimal 2MB. Server belum mendukung kompresi gambar otomatis.',
            ]);
        }

        $path = self::compressToTemporaryJpeg($file);
        $storedPath = trim($directory, '/').'/'.Str::uuid().'.jpg';

        Storage::disk('public')->put($storedPath, file_get_contents($path));
        @unlink($path);

        return $storedPath;
    }

    private static function canCompress(): bool
    {
        return function_exists('imagecreatefromstring')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagejpeg');
    }

    private static function compressToTemporaryJpeg(UploadedFile $file): string
    {
        $source = imagecreatefromstring(file_get_contents($file->getRealPath()));

        if (! $source) {
            throw ValidationException::withMessages([
                'photos' => 'Foto tidak dapat diproses. Gunakan format JPG, PNG, atau WebP.',
                'photo' => 'Foto tidak dapat diproses. Gunakan format JPG, PNG, atau WebP.',
            ]);
        }

        $source = self::fixJpegOrientation($source, $file);
        $bestPath = null;
        $bestSize = PHP_INT_MAX;

        foreach (self::MAX_DIMENSIONS as $maxDimension) {
            $canvas = self::resizedCanvas($source, $maxDimension);

            foreach (self::QUALITIES as $quality) {
                $temporaryPath = tempnam(sys_get_temp_dir(), 'pju-photo-').'.jpg';
                imagejpeg($canvas, $temporaryPath, $quality);
                $size = filesize($temporaryPath) ?: PHP_INT_MAX;

                if ($size <= self::MAX_BYTES) {
                    imagedestroy($canvas);
                    imagedestroy($source);

                    if ($bestPath) {
                        @unlink($bestPath);
                    }

                    return $temporaryPath;
                }

                if ($size < $bestSize) {
                    if ($bestPath) {
                        @unlink($bestPath);
                    }

                    $bestPath = $temporaryPath;
                    $bestSize = $size;
                } else {
                    @unlink($temporaryPath);
                }
            }

            imagedestroy($canvas);
        }

        imagedestroy($source);

        if ($bestPath) {
            @unlink($bestPath);
        }

        throw ValidationException::withMessages([
            'photos' => 'Foto terlalu besar dan belum bisa dikompres otomatis sampai di bawah 2MB.',
            'photo' => 'Foto terlalu besar dan belum bisa dikompres otomatis sampai di bawah 2MB.',
        ]);
    }

    private static function resizedCanvas($source, int $maxDimension)
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);

        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $canvas;
    }

    private static function fixJpegOrientation($source, UploadedFile $file)
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $source;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($source, 180, 0),
            6 => imagerotate($source, -90, 0),
            8 => imagerotate($source, 90, 0),
            default => $source,
        };
    }
}
