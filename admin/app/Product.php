<?php

namespace App;

use App\Contracts\IRecommendationItem;
use App\Services\ProductAttributeSyncer;
use Illuminate\Support\Facades\Log;

class Product extends Model implements IRecommendationItem
{
    use HasSlug, HasUser, Listable, Viewable, HasInteractionStat, HasMultipleSizeThumbnails;

    protected $appends = ['thumbnail_photos', 'discount'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Product $product) {
            $product->photos->each->delete();
        });
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_product_attribute')
            ->withPivot('value')
            ->using(ProductProductAttribute::class);
    }

    public function segments()
    {
        return $this->belongsToMany(Segment::class, 'segment_products')
            ->withPivot('priority_id')
            ->using(SegmentProduct::class);
    }

    public function interaction_items()
    {
        return $this->morphMany('App\InteractionItem', 'item');
    }

    public function getDiscountAttribute()
    {
        $attr = $this->attributes()->where('slug', ProductAttribute::DISCOUNT_SLUG)->first();
        return optional(optional($attr)->pivot)->value;
    }

    public function getStorageDisk()
    {
        return 'products';
    }

    public function syncPrices(array $prices, array $currencies, string $userId)
    {
        $this->prices()->delete();
        $data = collect($prices)
            ->map(function ($price, $i) use ($currencies, $userId) {
                return [
                    'currency_id'   => $currencies[$i],
                    'user_id'       => $userId,
                    'price'         => $price,
                ];
            })
            ->where('price', '!=', null)
            ->toArray();

        $this->prices()->createMany($data);
    }

    public function syncAttributes(array $ids = [], array $values = [])
    {
        (new ProductAttributeSyncer($this, $ids, $values))->sync();
    }

    public static function getAllExcept(Segment $segment)
    {
        $ids = $segment
            ->segment_products()
            ->with('product')
            ->with('priority')
            ->get()
            ->pluck('product_id');

        return Product::whereNotIn('id', $ids);
    }

    public function autocomplete(Segment $segment, $term)
    {
        return static::getAllExcept($segment)
            ->where('name', 'LIKE', '%' . $term . '%')
            ->get()
            ->map(function (Product $product) {
                return [
                    'id'    => $product->getKey(),
                    'value' => $product->name
                ];
            });
    }

    protected function excludedColumns()
    {
        return ['user_id', 'updated_at', 'link'];
    }

    // IRecommenderItem

    public function getId()
    {
        return $this->id;
    }

    /** productIds és proposerIds indexek alatt tároljuk a különböző elemeket.
     * Ez a metódus adja vissza, hogy melyikbe tartozik
     */
    public function getBucketName(): string
    {
        return 'productIds';
    }
}
