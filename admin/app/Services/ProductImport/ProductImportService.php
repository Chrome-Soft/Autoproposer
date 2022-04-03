<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\ProductImport;

use App\Contracts\IHttpClient;
use App\Currency;
use App\Product;
use App\ProductAttribute;
use App\ProductAttributeType;
use App\Rules\UniqueProductBatch;
use App\Services\ProductImport\Exceptions\AddDiscountException;
use App\Services\ProductImport\Exceptions\AddPhotoException;
use App\Services\ProductImport\Exceptions\AddPriceException;
use App\Services\ProductImport\Exceptions\AddValidityException;
use App\Services\ProductImport\Exceptions\CreateProductException;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductImportService
{
    /**
     * @var IHttpClient
     */
    private $client;
    /**
     * @var ProductImportStatistics
     */
    private $statistics;
    /** @var ProductAttribute[]|\Illuminate\Database\Eloquent\Collection */
    private $productAttributeCache;
    /**
     * @var ProductBatchCreate
     */
    private $productBatchCreate;
    /**
     * @var ImportOptions
     */
    private $options;

    public function __construct(IHttpClient $client, ProductImportStatistics $statistics, ImportOptions $options, ProductBatchCreate $productBatchCreate)
    {
        $this->client = $client;
        $this->client->setBaseUrl($options->baseUrl);
        $this->statistics = $statistics;

        $this->productAttributeCache = ProductAttribute::all();
        $this->productBatchCreate = $productBatchCreate;
        $this->options = $options;

        $this->setApiKey();
    }

    protected function setApiKey()
    {
        $data = $this->client->get($this->options->apiKeyEndpoint);
        $apiKey = hash('sha256', $data[0] . $this->options->apiKeyHash);

        $this->client->setHeader('secretKey', $apiKey);
    }

    public function import()
    {
        $externalProducts = $this->client->get($this->options->endpoint);
        $this->statistics->all($externalProducts);

        $batchResult = $this->productBatchCreate->createProducts($externalProducts);

        foreach ($externalProducts as $externalProduct) {
            try {
                $product = $this->getProduct($batchResult->products, $externalProduct, $batchResult->invalidNames);
                $hasError = false;

                try {
                    $this->addPhotos($externalProduct, $product);
                } catch (AddPhotoException $ex) {
                    $this->handleError($product, $ex);
                    $hasError = true;
                }

                try {
                    $this->addDiscounts($externalProduct, $product);
                } catch (AddDiscountException $ex) {
                    $this->handleError($product, $ex);
                    $hasError = true;
                }

                try {
                    $this->addCardValidity($externalProduct, $product);
                } catch (AddValidityException $ex) {
                    $this->handleError($product, $ex);
                    $hasError = true;
                }

                try {
                    $this->addTicketValidity($externalProduct, $product);
                } catch (AddValidityException $ex) {
                    $this->handleError($product, $ex);
                    $hasError = true;
                }

                try {
                    $this->addPrices($externalProduct, $product);
                } catch (AddPriceException $ex) {
                    $this->handleError($product, $ex);
                    $hasError = true;
                }

                if (!$hasError)
                    $this->statistics->passed($product);

            } catch (CreateProductException $pe) {
                $this->statistics->failed(new Product(['name' => Arr::get($externalProduct, 'name', 'Ismeretlen termék') ?? 'Ismeretlen termék']), $pe);
                $this->logError($pe);
            } catch (\Exception $ex) {
                $unknownError = new \Exception('Ismeretlen hiba történt');
                if (isset($product) && $product) {
                    $this->statistics->createdWithError($product, $unknownError);
                    $this->logError($ex, $product);
                } else {
                    $this->statistics->failed(new Product(['name' => Arr::get($externalProduct, 'name', 'Ismeretlen termék') ?? 'Ismeretlen termék']), $unknownError);
                    $this->logError($ex);
                }
            }
        }

        return $this->statistics->report();
    }

    /**
     * @param $externalProduct
     * @param Product $product
     * @throws AddPhotoException
     */
    protected function addPhotos(array $externalProduct, Product $product): void
    {
        try {
            $photos = Arr::get($externalProduct, 'pictures', []);
            if (empty($photos)) return;

            $defaultPhoto = collect($photos)->where('isDefault', true)->first();
            if (!$defaultPhoto) $defaultPhoto = collect($photos)->first();

            if (!$defaultPhoto['fullSize']) return;

            $url = Str::startsWith('http', $defaultPhoto['fullSize']) || Str::startsWith('https', $defaultPhoto['fullSize'])
                ? $defaultPhoto['fullSize']
                : "http://{$defaultPhoto['fullSize']}";

            DB::table('product_photos')->insert([
                'product_id'    => $product->id,
                'image_path'    => $url,
                'user_id'       => auth()->id()
            ]);
        } catch (\Exception $ex) {
            throw new AddPhotoException('Nem sikerült elmenteni a termék képeit. Lehetséges okok: hiányzó URL', 0, $ex);
        }
    }

    /**
     * @param $externalProduct
     * @param Product $product
     * @throws AddPriceException
     */
    protected function addPrices(array $externalProduct, Product $product): void
    {
        try {
            $currencyCache = Cache::get('currencies');

            $pricesData = Arr::get($externalProduct, 'prices', null);
            if (!$pricesData) return;

            $values = [];
            foreach ($pricesData as $data) {
                $values[] = [
                    'product_id'    => $product->id,
                    'currency_id'   => $currencyCache->where('symbol', $data['currency'])->first()->id,
                    'user_id'       => auth()->id(),
                    'price'         => $data['price']
                ];
            }

            DB::table('product_prices')->insert($values);

        } catch (\Exception $ex) {
            throw new AddPriceException('Nem sikerült elmenteni a termék árait. Lehetséges okok: ismeretlen valuta, ismétlődő valuta, hiányzó ár', 0, $ex);
        }
    }

    /**
     * @param $externalProduct
     * @param Product $product
     * @throws AddValidityException
     */
    protected function addCardValidity(array $externalProduct, Product $product): void
    {
        if ($externalProduct['type'] !== 'card_types') return;

        $validity = Arr::get($externalProduct, 'validity', null);
        if (empty($validity)) throw new AddValidityException('Nem sikerült elmenteni a kártya érvényességére vonatkozó adatokat.');

        try {
            $attribute = is_array($validity)
                ? $this->productAttributeCache->where('slug', 'ervenyessegi-intervallum')->first()
                : $this->productAttributeCache->where('slug', 'ervenyessegi-ido')->first();

            $value = is_array($validity)
                ? json_encode(array_values($validity))
                : $validity;

            $product->attributes()->attach($attribute->id, [
                'value' => $value
            ]);
        } catch (\Exception $ex) {
            throw new AddValidityException('Nem sikerült elmenteni a kártya érvényességére vonatkozó adatokat.', 0, $ex);
        }
    }

    /**
     * @param $externalProduct
     * @return array
     */
    protected function groupDiscounts(array $discounts): array
    {
        $discountsGrouped = [];
        foreach ($discounts as $discount) {
            if (!isset($discountsGrouped[$discount['name']])) {
                $discountsGrouped[$discount['name']] = [];
            }

            $discountsGrouped[$discount['name']][] = $discount['partner'];
        }
        return $discountsGrouped;
    }

    /**
     * @param array $discountsGrouped
     * @param Product $product
     * @throws AddDiscountException
     */
    protected function addDiscounts(array $externalProduct, Product $product): void
    {
        $getOrCreateDiscountAttr = function ($name) {
            $discountAttr = $this->productAttributeCache->where('name', $name)->first();

            if ($discountAttr) return $discountAttr;

            $discountAttr = ProductAttribute::createImported($name);

            $this->productAttributeCache->add($discountAttr);
            return $discountAttr;
        };

        try {
            $discounts = Arr::get($externalProduct, 'discounts', []);
            $attributes = [];

            foreach ($this->groupDiscounts($discounts) as $name => $partners) {
                $discountAttr = $getOrCreateDiscountAttr($name);

                $partnerStr = collect($partners)
                    ->map(function ($x) {
                        return "{$x['name']} {$x['address']}";
                    })
                    ->join(' ');

                $attributes[] = [
                    'product_id'            => $product->id,
                    'product_attribute_id'  => $discountAttr->id,
                    'value'                 => $partnerStr
                ];
            }

            DB::table('product_product_attribute')->insert($attributes);

        } catch (\Exception $ex) {
            throw new AddDiscountException('Nem sikerült elmenteni a termék kedvezményeit. Lehetséges okok: hiányzó partner adatok, hibás kedvezmény név', 0, $ex);
        }
    }

    /**
     * @param $externalProduct
     * @param $field
     * @param Product $product
     * @throws AddValidityException
     */
    protected function addTicketValidity($externalProduct, Product $product)
    {
        if ($externalProduct['type'] !== 'tickets') return;

        $mapper = [
            'whenToBuy'     => 'vasarlasi-intervallum',
            'whenToSwitch'  => 'ervenyessegi-intervallum'
        ];

        try {
            foreach ($mapper as $field => $slug) {
                if (!Arr::get($externalProduct, $field, null)) throw new AddValidityException('Nem sikerült elmenteni a jegy érvényességi és/vagy vásárlási intervallumára vonatkozó adatokat.');

                $attr = $this->productAttributeCache->where('slug', $slug)->first();
                $values[] = [
                    'product_id'            => $product->id,
                    'product_attribute_id'  => $attr->id,
                    'value'                 => json_encode(array_values($externalProduct[$field]))
                ];
            }

            DB::table('product_product_attribute')->insert($values);
        } catch (\Exception $ex) {
            throw new AddValidityException('Nem sikerült elmenteni a jegy érvényességi és/vagy vásárlási intervallumára vonatkozó adatokat.',0, $ex);
        }
    }

    protected function logError(\Exception $ex, Product $product = null)
    {
        Log::error('PRODUCT IMPORT ERROR Hiba termék importálása közben:');
        if ($product) Log::error($product);

        Log::error($ex);
    }

    /**
     * @param $products
     * @param $externalProduct
     * @return array
     * @throws CreateProductException
     */
    protected function getProduct($products, $externalProduct, $failedNames): Product
    {
        $ex = new CreateProductException('Nem sikerült létrehozni a terméket. Lehetséges okok: már létezik ilyen termék, üres termék név');

        if ($failedNames->contains($externalProduct['name']))
            throw $ex;

        $product = $products->firstWhere('name', $externalProduct['name']);
        if (!$product) throw $ex;

        return $product;
    }

    /**
     * @param $product
     * @param $ex
     */
    protected function handleError($product, $ex): void
    {
        $this->statistics->createdWithError($product, $ex);
        $this->logError($ex, $product);
    }
}
