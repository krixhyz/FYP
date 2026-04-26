<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Throwable;

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
        return self::resolveImageUrl($path);
    }

    /**
     * Get the correct URL for dispute evidence image paths.
     *
     * @param string|null $path
     * @return string
     */
    public static function getDisputeImageUrl($path)
    {
        return self::resolveImageUrl($path);
    }

    /**
     * Resolve image URL for current filesystem strategy.
     */
    private static function resolveImageUrl($path): string
    {
        if (!$path) {
            return asset('images/placeholder.png');
        }

        // If already a full URL, return as-is regardless of disk.
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = (string) config('filesystems.default');
        if ($disk === 'cloudinary' && self::isCloudinaryConfigured()) {
            try {
                return Storage::disk('cloudinary')->url($path);
            } catch (Throwable $e) {
                // Fall back to local URL generation to avoid breaking page render.
            }
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private static function isCloudinaryConfigured(): bool
    {
        $cloudinary = (array) config('filesystems.disks.cloudinary', []);

        if (!empty($cloudinary['url'])) {
            return true;
        }

        return !empty($cloudinary['cloud'])
            && !empty($cloudinary['key'])
            && !empty($cloudinary['secret']);
    }
}
