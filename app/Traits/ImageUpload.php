<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ImageUpload
{
    public function imageUpload($image, $path = null)
    {
        $imageName = Str::uuid().'.'.$image->getClientOriginalExtension();

        $fullPath = 'images/'.$path;

        Storage::disk('public')->putFileAs($fullPath, $image, $imageName);

        return $imageName;
    }

    public function deleteImage($image, $path = null)
    {
        $fullPath = 'images/'.$path;

        if (Storage::disk('public')->exists($fullPath.$image)) {
            Storage::disk('public')->delete($fullPath.$image);
        }
    }
}
