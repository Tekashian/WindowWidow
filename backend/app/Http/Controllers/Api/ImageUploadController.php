<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    /**
     * Upload an image
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ]);

        try {
            $image = $request->file('image');
            
            // Generate unique filename
            $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
            
            // Store in public disk
            $path = $image->storeAs('images/windows', $filename, 'public');
            
            // Generate URL
            $url = Storage::url($path);
            
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'url' => $url,
                'path' => $path,
                'filename' => $filename
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an image
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            $path = $request->input('path');
            
            // Remove /storage/ prefix if present
            $path = str_replace('/storage/', '', $path);
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }
}
