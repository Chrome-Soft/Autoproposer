<?php

namespace App;

use App\Contracts\IRecommendationItem;

class ProposerItem extends Model implements IRecommendationItem
{
    use HasUser, HasInteractionStat, HasMultipleSizeThumbnails;

    protected $appends = ['all_present', 'all_view', 'view_ratio', 'thumbnail_photos'];
    protected $with = ['type'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (ProposerItem $item) {
            $item->photos->each->delete();
        });
    }

    public function path($uri = ''): string
    {
        return "/proposers/{$this->proposer->slug}/items/{$this->id}/{$uri}";
    }

    public function proposer()
    {
        return $this->belongsTo(Proposer::class);
    }

    public function type()
    {
        return $this->hasOne(ProposerItemType::class, 'id', 'type_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function interaction_items()
    {
        return $this->morphMany('App\InteractionItem', 'item');
    }

    public function photos()
    {
        return $this->hasMany(ProposerItemPhoto::class);
    }

    public function getStorageDisk()
    {
        return 'proposer-items';
    }

    public function getThumbnailPhotosAttribute()
    {
        // Kép feltöltés
        if ($this->type_id == 2) {
            return [
                'small'     => $this->small_photo,
                'medium'    => $this->medium_photo,
                'large'     => $this->large_photo,
            ];
        } elseif ($this->type_id == 3) {
            return $this->product->thumbnail_photos;
        }

        return [];
    }

    public function photoFallback()
    {
        // TODO: Implement photoFallback() method.
    }

    public function getId()
    {
        return $this->product_id;
    }

    /** productIds és proposerIds indexek alatt tároljuk a különböző elemeket.
     * Ez a metódus adja vissza, hogy melyikbe tartozik
     */
    public function getBucketName(): string
    {
        return 'proposerIds';
    }

    public function isInBuckets(array $ids)
    {
        return (in_array($this->id, $ids['proposerIds']) || in_array($this->product_id, $ids['productIds']));
    }
}
