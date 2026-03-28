<?php

namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Http\UploadedFile;

class FirebaseStorageService
{
    protected $bucket;

    public function __construct()
    {
        $this->bucket = Firebase::storage()->getBucket();
    }

    public function upload(UploadedFile $file, string $folder): string
    {
        $fileName   = $folder . '/' . uniqid() . '_' . $file->getClientOriginalName();
        $fileStream = fopen($file->getRealPath(), 'r');

        $object = $this->bucket->upload($fileStream, [
            'name'          => $fileName,
            'predefinedAcl' => 'publicRead',  // makes the file publicly accessible
        ]);

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
}
