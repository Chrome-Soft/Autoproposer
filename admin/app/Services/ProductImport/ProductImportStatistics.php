<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\ProductImport;


use App\Product;
use Illuminate\Support\Collection;

class ProductImportStatistics
{
    /** @var int */
    public $all = 0;
    /** @var int */
    public $passed = 0;
    /** @var int */
    public $failed = 0;
    /** @var int */
    public $createdWithError = 0;

    public $failedProducts = [];
    public $passedProducts = [];

    public function all(array $externalProducts)
    {
        $this->all = count($externalProducts);
    }

    public function passed(\App\Product $product)
    {
        $this->passed++;
        $this->passedProducts[] = $product;
    }

    public function createdWithError(Product $product, \Exception $ex)
    {
        $this->addFailedProduct($product, $this->createdWithError, $ex);
    }

    public function failed(\App\Product $product, \Exception $ex)
    {
        $this->addFailedProduct($product, $this->failed, $ex);
    }

    protected function addFailedProduct(Product $product, &$counter, $ex)
    {
        if (!isset($this->failedProducts[$product->slug])) {
            $counter++;
            $this->failedProducts[$product->slug] = [
                'product'   => $product,
                'errors'    => []
            ];
        }

        $this->failedProducts[$product->slug]['errors'][] = $ex->getMessage();
    }

    public function report()
    {
        return $this;
    }
}
