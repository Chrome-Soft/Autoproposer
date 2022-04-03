<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App;


use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasMultipleSizeThumbnails
{
    abstract public function photos(): HasMany;
    abstract public function getStorageDisk(): string;

    public function getSmallPhotoAttribute()
    {
        $small = $this->photos()
            ->where('image_path', 'LIKE', '%small%')
            ->first();

        return either($small, $this->photoFallback());
    }

    public function getMediumPhotoAttribute()
    {
        $medium = $this->photos()
            ->where('image_path', 'LIKE', '%medium%')
            ->first();

        return either($medium, $this->photoFallback());
    }

    public function getLargePhotoAttribute()
    {
        $large = $this->photos()
            ->where('image_path', 'LIKE', '%large%')
            ->first();

        return either($large, $this->photoFallback());
    }

    public function getThumbnailPhotosAttribute()
    {
        return [
            'small' => $this->small_photo,
            'medium' => $this->medium_photo,
            'large' => $this->large_photo,
        ];
    }

    /**
     * Importált termékeknél lehet olyan, hogy külső URL lesz a kép címe, ezért
     * nem lesz hozzá small, medium, large. Ilyenkor a külső URL -re
     * fallback -elünk. ProposerItem -nél se gond, ha van ilyen
     */
    protected function photoFallback()
    {
        return $this->photos()
            ->first();
    }
}
