<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

abstract class PhotoPivot extends Model
{
    protected $appends = ['public_path', 'public_url'];

    public function getImagePath()
    {
        return $this->image_path;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (PhotoPivot $photo) {
            Storage::disk('public')->delete($photo->getImagePath());
        });
    }

    public function getPublicPathAttribute()
    {
        return Storage::url($this->image_path);
    }

    public function getPublicUrlAttribute()
    {
        return URL::to('/' ) . Storage::url($this->image_path);
    }
}
