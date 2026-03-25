<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesImageUpload
{
    public function uploadImage(
        UploadedFile $image,
        string $folder = 'uploads'
    ): string {
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();
        $path = "{$folder}/{$filename}";

        Storage::disk('public')->putFileAs($folder, $image, $filename);

        return $path; // store this in DB
    }

    /**
     * Replace and delete old image.
     */
    public function updateImage(
        ?UploadedFile $newImage,
        ?string $oldImagePath,
        string $folder = 'uploads'
    ): ?string {
        if (!$newImage) return $oldImagePath;

        $this->deleteImage($oldImagePath);

        return $this->uploadImage($newImage, $folder);
    }

    /**
     * Delete image from storage.
     */
    public function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

// trait HandlesImageUpload
// {
//     public function uploadImage(
//         UploadedFile $image,
//         string $folder = 'unknown'
//     ): string {
//         $filename = uniqid() . '.' . $image->getClientOriginalExtension();
//         $path = "{$folder}/{$filename}";

//         // Save to otp_upload disk
//         Storage::disk('opt_uploads')->putFileAs($folder, $image, $filename);

//         return $path; // store this path in DB
//     }

//     /**
//      * Replace and delete old image.
//      */
//     public function updateImage(
//         ?UploadedFile $newImage,
//         ?string $oldImagePath,
//         string $folder = 'uploads'
//     ): ?string {
//         if (!$newImage) return $oldImagePath;

//         $this->deleteImage($oldImagePath);

//         return $this->uploadImage($newImage, $folder);
//     }

//     /**
//      * Delete image from storage.
//      */
//     public function deleteImage(?string $path): void
//     {
//         if ($path && Storage::disk('opt_uploads')->exists($path)) {
//             Storage::disk('opt_uploads')->delete($path);
//         }
//     }
// }