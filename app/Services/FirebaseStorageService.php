<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class FirebaseStorageService
{
    protected $bucket;

    public function __construct()
    {
        try {
            $this->bucket = app('firebase.storage')->getBucket();
        } catch (Throwable $exception) {
            $this->bucket = null;

            Log::warning('Firebase storage unavailable. Falling back to local public disk.', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function upload(UploadedFile $file, string $folder): string
    {
        $extension = $file->extension() ?: 'bin';
        $fileName = $folder . '/' . (string) Str::uuid() . '.' . $extension;

        if (!$this->bucket) {
            return $this->uploadToLocalDisk($file, $folder, basename($fileName));
        }

        [$fileStream, $contentType] = $this->buildUploadStream($file);

        try {
            $object = $this->bucket->upload($fileStream, [
                'name'          => $fileName,
                'predefinedAcl' => 'publicRead',
                'metadata'      => ['contentType' => $contentType],
            ]);

            return sprintf(
                'https://storage.googleapis.com/%s/%s',
                $this->bucket->name(),
                $object->name()
            );
        } catch (Throwable $exception) {
            Log::warning('Firebase upload failed. Falling back to local public disk.', [
                'file' => $fileName,
                'error' => $exception->getMessage(),
            ]);

            return $this->uploadToLocalDisk($file, $folder, basename($fileName));
        } finally {
            if (is_resource($fileStream)) {
                fclose($fileStream);
            }
        }
    }

    public function delete(string $fileUrl): void
    {
        if (!$fileUrl) {
            return;
        }

        if ($this->bucket && str_contains($fileUrl, 'storage.googleapis.com')) {
            try {
                $path = parse_url($fileUrl, PHP_URL_PATH);
                $path = ltrim(str_replace('/' . $this->bucket->name() . '/', '', (string) $path), '/');

                if ($path !== '') {
                    $object = $this->bucket->object($path);

                    if ($object->exists()) {
                        $object->delete();
                    }

                    return;
                }
            } catch (Throwable $exception) {
                Log::warning('Firebase delete failed. Attempting local delete fallback.', [
                    'file_url' => $fileUrl,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->deleteFromLocalDisk($fileUrl);
    }

    private function uploadToLocalDisk(UploadedFile $file, string $folder, string $filename): string
    {
        $storedPath = $file->storeAs($folder, $filename, 'public');

        return Storage::url($storedPath);
    }

    private function deleteFromLocalDisk(string $fileUrl): void
    {
        $path = parse_url($fileUrl, PHP_URL_PATH);

        if (!$path) {
            $path = $fileUrl;
        }

        $normalized = ltrim((string) $path, '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }

    private function buildUploadStream(UploadedFile $file): array
    {
        $fallback = [fopen($file->getRealPath(), 'r'), $file->getMimeType() ?: 'application/octet-stream'];
        $mime = $file->getMimeType() ?: '';

        if (!str_starts_with($mime, 'image/') || !extension_loaded('gd')) {
            return $fallback;
        }

        $raw = @file_get_contents($file->getRealPath());
        if ($raw === false) {
            return $fallback;
        }

        $image = @imagecreatefromstring($raw);
        if ($image === false) {
            return $fallback;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);
            return $fallback;
        }

        $maxWidth = 1600;
        $target = $image;

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round(($height / $width) * $newWidth);
            $resized = imagecreatetruecolor($newWidth, $newHeight);

            if (in_array($mime, ['image/png', 'image/gif'], true)) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $target = $resized;
        }

        $stream = fopen('php://temp', 'w+b');
        $contentType = $mime;

        if ($mime === 'image/png') {
            imagepng($target, $stream, 6);
        } elseif ($mime === 'image/gif') {
            imagegif($target, $stream);
        } else {
            imagejpeg($target, $stream, 82);
            $contentType = 'image/jpeg';
        }

        rewind($stream);

        if ($target !== $image) {
            imagedestroy($target);
        }
        imagedestroy($image);

        return [$stream, $contentType];
    }
}
