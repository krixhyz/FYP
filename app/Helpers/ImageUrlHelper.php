<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ImageUrlHelper
{
    /**
     * Get the correct image URL for a product image, supporting local and Cloudinary.
     *
     * @param string|null $path
     * @return string
     */
    public static function getProductImageUrl($path)
    {
        if (!$path) {
            return asset('images/placeholder.png');
        }

        $disk = config('filesystems.default');
        if ($disk === 'cloudinary') {
            // If already a full Cloudinary URL, return as is
            if (str_starts_with($path, 'http')) {
                return $path;
            }
            // Use Storage::disk('cloudinary')->url()
            return Storage::disk('cloudinary')->url($path);
        }
        // Local/public fallback
        return asset('storage/' . ltrim($path, '/'));
    }
}
