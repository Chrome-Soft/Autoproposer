<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Interaction extends Model
{
    protected $with = ['items'];
    public function addItems(array $items)
    {
        $itemsData = array_map(function (array $item) {
            return [
                'item_id'           => $item['id'],
                'item_name'         => Arr::get($item, 'name'),
                'item_type'         => static::mapType($item),
                'buy_quantity'      => Arr::get($item, 'qty'),
                'buy_unit_price'    => Arr::get($item, 'unitPrice')
            ];
        }, $items);

        $this->items()->createMany($itemsData);
    }

    public function items()
    {
        return $this->hasMany(InteractionItem::class);
    }

    public static function storeWithItems($type, $cookieId, array $items, $userId = null)
    {
        $interaction = new Interaction;
        $interaction->type = $type;
        $interaction->user_id = $userId;
        $interaction->cookie_id = $cookieId;

        $interaction->save();
        $interaction->addItems($items);
    }

    public static function mapType(array $item)
    {
        $mapper = [
            'product'   => 'Product',
            'html'      => 'ProposerItem',
            'image'     => 'ProposerItem'
        ];

        if (!Arr::get($item, 'name')) {
            return "App\\ProposerItem";
        }

        $type = $item['type'];
        return "App\\{$mapper[$type]}";
    }
}
