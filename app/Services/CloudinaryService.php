<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Resize;
use Exception;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
     protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ]
        ]);
    }

    public function uploadImage($file, $folder = 'uploads', $options = [])
    {
        try {
            $defaultOptions = [
                'folder' => $folder,
                'use_filename' => true,
                'unique_filename' => true,
                'overwrite' => false,
                'resource_type' => 'image'
            ];

            $uploadOptions = array_merge($defaultOptions, $options);

            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                $uploadOptions
            );

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'width' => $result['width'],
                'height' => $result['height'],
                'format' => $result['format'],
                'size' => $result['bytes']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function deleteImage($publicId)
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);
            return $result['result'] === 'ok';
        } catch (Exception $e) {
            return false;
        }
    }

    public function getOptimizedUrl($publicId, $width = null, $height = null, $quality = 'auto')
    {
        $transformation = [];

        if ($width && $height) {
            $transformation[] = ['width' => $width, 'height' => $height, 'crop' => 'fill'];
        } elseif ($width) {
            $transformation[] = ['width' => $width, 'crop' => 'scale'];
        }

        $transformation[] = ['quality' => $quality];
        $transformation[] = ['fetch_format' => 'auto'];

        return $this->cloudinary->image($publicId)
            ->addTransformation($transformation)
            ->toUrl();
    }
}
