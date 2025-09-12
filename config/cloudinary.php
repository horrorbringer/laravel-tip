<?php

return [
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
    'secure' => true,
    'file_size' => 10485760, // 10MB max file size
    'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
];
