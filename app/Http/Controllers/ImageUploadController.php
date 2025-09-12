<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
   protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }
    public function index()
    {
        return view('upload-image');
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Max 10MB
            'folder' => 'nullable|string|max:100'
        ]);

        $folder = $request->input('folder', 'uploads');

        $result = $this->cloudinaryService->uploadImage(
            $request->file('image'),
            $folder
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $result['error']
        ], 500);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'public_id' => 'required|string'
        ]);

        $deleted = $this->cloudinaryService->deleteImage($request->input('public_id'));

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Image deleted successfully' : 'Failed to delete image'
        ]);
    }

    public function getOptimizedUrl(Request $request): JsonResponse
    {
        $request->validate([
            'public_id' => 'required|string',
            'width' => 'nullable|integer|min:1|max:2000',
            'height' => 'nullable|integer|min:1|max:2000',
            'quality' => 'nullable|string|in:auto,best,good,eco,low'
        ]);

        $url = $this->cloudinaryService->getOptimizedUrl(
            $request->input('public_id'),
            $request->input('width'),
            $request->input('height'),
            $request->input('quality', 'auto')
        );

        return response()->json([
            'success' => true,
            'url' => $url
        ]);
    }

    public function uploadWithDatabase(Request $request): JsonResponse
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        'folder' => 'nullable|string|max:100'
    ]);

    $folder = $request->input('folder', 'uploads');

    $result = $this->cloudinaryService->uploadImage(
        $request->file('image'),
        $folder
    );

    if ($result['success']) {
        // Save to database
        $image = Image::create([
            'public_id' => $result['public_id'],
            'url' => $result['url'],
            'width' => $result['width'],
            'height' => $result['height'],
            'format' => $result['format'],
            'size' => $result['size'],
            'folder' => $folder,
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => $image
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Upload failed: ' . $result['error']
    ], 500);
    }
}
