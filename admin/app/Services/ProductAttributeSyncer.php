<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services;

use App\Product;
use App\ProductAttribute;
use Illuminate\Support\Collection;

class ProductAttributeSyncer
{
    /**
     * @var Product
     */
    private $product;
    /**
     * @var array
     */
    private $attributeIds;
    /**
     * @var array
     */
    private $attributeValues;

    public function __construct(Product $product, array $attributeIds = [], array $attributeValues = [])
    {
        $this->product = $product;
        $this->attributeIds = $attributeIds;
        $this->attributeValues = $attributeValues;
    }

    /**
     * Törli a termék összes attribútumát, és hozzáadja az $this->attributeIds -ban szereplőket
     * a $this->>attributeValues -ban szereplő értékekkel
     */
    public function sync()
    {
        $this->product->attributes()->detach();

        $extraAttrs = ProductAttribute::getExtraAttributesBy($this->attributeIds);
        $extraIdIndices = $this->findExtraAttributeIndicesIn($extraAttrs);
        $result = $this->groupAttributeValuesByIds($extraIdIndices);

        foreach ($result as $id => $value) {
            $this->product->attributes()->attach($id, [
                'value' => is_array($value) ? json_encode($value) : $value
            ]);
        }
    }

    /**
     * Visszaadja a $this->attributeIds -ból azokat az indexeket, amik extra attribútumokhoz tartoznak az $extraAttrs alapján
     * @param Collection $extraAttrs
     * @return array
     */
    private function findExtraAttributeIndicesIn(Collection $extraAttrs): array
    {
        return $extraAttrs
            ->map(function ($attr) { return array_search($attr->id, $this->attributeIds); })
            ->reject(function ($x) { return $x === false; })
            ->toArray();
    }

    /**
     * Visszaad egy tömböt, aminek az indexei attribútum ID -k lesznek az értékei pedig attribútum értékek.
     * Ha extra attribútumról van szó (több, mint 1 érték), akkor array lesz
     * @param array $extraIdIndices
     * @return array
     */
    private function groupAttributeValuesByIds(array $extraIdIndices): array
    {
        $counter = -1;
        $result = [];

        foreach ($this->attributeIds as $i => $id) {
            if (in_array($i, $extraIdIndices)) {
                $result[$id] = [$this->attributeValues[$counter + 1], $this->attributeValues[$counter + 2]];
                $counter += 2;
            } else {
                $counter++;
                $result[$id] = $this->attributeValues[$counter];
            }
        }

        return $result;
    }
}
