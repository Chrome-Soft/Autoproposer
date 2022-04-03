<?php

namespace App;

use Illuminate\Support\Facades\Storage;

class Proposer extends Model
{
    use HasSlug, HasUser, Listable, Viewable;

    protected $with = ['partner', 'user'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Proposer $proposer) {
            $proposer->cleanPageUrl();
        });

        static::updating(function (Proposer $proposer) {
            $proposer->cleanPageUrl();
        });
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function items()
    {
        return $this->hasMany(ProposerItem::class);
    }

    protected function excludedColumns()
    {
        return ['user_id', 'updated_at'];
    }

    protected function excludedViewFields(): array
    {
        $embedded = ProposerType::getAllFromCache()->where('key', ProposerType::TYPE_EMBEDDED)->first();
        $popup = ProposerType::getAllFromCache()->where('key', ProposerType::TYPE_POPUP)->first();

        switch ($this->type_id) {
            case $embedded->id:
            case $popup->id:
                return ['id','updated_at','slug','deleted_at', 'width', 'height'];
            default:
                return ['id','updated_at','slug','deleted_at','page_url'];
        }
    }

    protected function customCasters()
    {
        return [
            'width' => function ($value) { return "{$value}px"; },
            'height' => function ($value) { return "{$value}px"; }
        ];
    }

    protected function excludedFromFilters()
    {
        return ['partner_id'];
    }
    
    protected function cleanPageUrl()
    {
        $this->page_url = static::getCleanedPageUrl($this->page_url);
    }

    public static function getCleanedPageUrl($pageUrl)
    {
        return str_replace('/', '', $pageUrl);
    }

    protected function relationMappers()
    {
        return [
            'partner_id' => [
                'table'     => 'partners',
                'column'    => 'name'
            ],
            'type_id'   => [
                'table'     => 'proposer_types',
                'column'    => 'name'
            ]
        ];
    }

    public function getItemsWithLabels()
    {
        return $this->items
            ->map(function ($x) {
                $x->type_key = (ProposerItemType::getFromCache($x->type_id))->key;
                return $x;
            })
            ->map(function ($x) {
                if ($x->type_key == ProposerItemType::TYPE_PRODUCT) {
                    $x->link = $x->product->link;
                    $x->name = $x->product->name;
                    $x->discount = $x->product->discount;
                }

                return $x;
            })
            ->map(function ($x) {
                if ($x->type_key == ProposerItemType::TYPE_HTML) {
                    $x->html_content = str_replace('&lt;', '<', $x->html_content);
                    $x->html_content = str_replace('&gt;', '>', $x->html_content);
                }

                return $x;
            });
    }
}
