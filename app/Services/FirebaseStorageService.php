<?php

namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FirebaseStorageService
{
    protected $bucket;

    public function __construct()
    {
        $this->bucket = Firebase::storage()->getBucket();
    }

    public function upload(UploadedFile $file, string $folder): string
    {
        $extension = $file->extension() ?: 'bin';
        $fileName = $folder . '/' . (string) Str::uuid() . '.' . $extension;

        [$fileStream, $contentType] = $this->buildUploadStream($file);

        $object = $this->bucket->upload($fileStream, [
            'name'          => $fileName,
            'predefinedAcl' => 'publicRead',  // makes the file publicly accessible
            'metadata'      => ['contentType' => $contentType],
        ]);

        if (is_resource($fileStream)) {
            fclose($fileStream);
        }

        // Return the public URL
        return sprintf(
            'https://storage.googleapis.com/%s/%s',
            $this->bucket->name(),
            $object->name()
        );
    }

    public function delete(string $fileUrl): void
    {
        // Extract the file path from the full URL
        $path = parse_url($fileUrl, PHP_URL_PATH);
        $path = ltrim(str_replace('/' . $this->bucket->name() . '/', '', $path), '/');

        $object = $this->bucket->object($path);

        if ($object->exists()) {
            $object->delete();
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
