<?php

namespace App;

class ProposerItemType extends Model
{
    use Cacheable;

    const TYPE_HTML     = 'html';
    const TYPE_PRODUCT  = 'product';
    const TYPE_IMAGE    = 'image';

    public function propserItem()
    {
        return $this->belongsTo(ProposerItem::class);
    }

    static public function getByKey($key)
    {
        return ProposerItemType::where('key', $key)->first();
    }
}
