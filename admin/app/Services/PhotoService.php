<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services;


use http\Exception\BadMethodCallException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PhotoService
{
    public function uploadPhotos($model, array $photos, $userId)
    {
        if (empty($photos)) {
            return;
        }

        $model->photos->each->delete();

        foreach ($photos as $photo) {
            /** @var UploadedFile $photo */
            $pivotData = [];
            $paths = $this->uploadMultipleSizes($photo, $model->getStorageDisk());

            foreach ($paths as $path) {
                $pivotData[] = [
                    'user_id'       => $userId,
                    'image_path'    => $path
                ];
            }

            $model->photos()->createMany($pivotData);
        }
    }

    public function uploadMultipleSizes(UploadedFile $photo, $directory, PhotoSize $sizes = null): array
    {
        $sizes = either($sizes, new PhotoSize(90, 160));
        $paths = [];

        foreach ($sizes->toArray() as $name => $height) {
            $photoResized = Image::make($photo->getRealPath())
                ->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->stream($photo->clientExtension(), 90);

            $path = "{$directory}/{$name}-{$photo->hashName()}";
            Storage::disk('public')->put($path, $photoResized);

            $paths[] = $path;
        }

        return $paths;
    }

    public function uploadBannerSize(UploadedFile $photo, $directory)
    {
        $photoResized = Image::make($photo->getRealPath())
            ->resize(null, 90, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->stream($photo->clientExtension(), 90);

        $path = "{$directory}/{$photo->hashName()}";
        Storage::disk('public')->put($path, $photoResized);

        return $path;
    }
}

class PhotoSize {
    public $smallHeight = 90;
    public $mediumHeight = 160;
    public $largeHeight = 0;

    public function __construct(int $smallHeight, int $mediumHeight, int $largeHeight = 0)
    {
        if (!$this->hasSize($this->smallHeight) && !$this->hasSize($this->mediumHeight) && !$this->hasSize($this->largeHeight)) {
            throw new BadMethodCallException('At least one size is required');
        }

        $this->smallHeight = $smallHeight;
        $this->mediumHeight = $mediumHeight;
        $this->largeHeight = $largeHeight;
    }

    public function toArray()
    {
        $sizes = [];

        $setSize = function ($index) use (&$sizes) {
            $prop = $index . 'Height';
            if ($this->hasSize($this->{$prop})) {
                $sizes[$index] = $this->{$prop};
            }
        };

        $setSize('small');
        $setSize('medium');
        $setSize('large');

        return $sizes;
    }

    private function hasSize($size): bool
    {
        return $size && $size !== 0;
    }
}
