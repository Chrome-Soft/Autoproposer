<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\ProductImport;


use App\Product;
use App\Rules\UniqueProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductBatchCreate
{
    public function createProducts(array $externalProducts): BatchResult
    {
        $batchData = $this->createBatchData($externalProducts);
        [$batchData, $invalidNames] = $this->validateBatchData($batchData);

        // Beszúrja az összes terméket egy batch insertben, majd lekérdezi őket egy select -vel
        $this->insertProducts($batchData);
        $products = $this->getProducts($externalProducts);

        return new BatchResult($products, $invalidNames);
    }

    protected function insertProducts(BatchData $batchData)
    {
        $values = [];
        foreach ($batchData->names as $i => $name) {
            $values[] = [
                'name'          => $name,
                'description'   => $batchData->descriptions[$i],
                'link'          => Arr::get($batchData->links, $i),
                'slug'          => $batchData->slugs[$i],
                'user_id'       => auth()->id(),
                'created_at'    => Carbon::now()
            ];
        }

        try {
            DB::table('products')->insert($values);
        } catch (\Exception $ex) {
            Log::error($ex);
        }
    }

    /**
     * @param array $externalProducts
     * @return Collection
     */
    protected function getProducts(array $externalProducts): Collection
    {
        $externalNames = collect($externalProducts)->map(function ($x) {
            return $x['name'];
        });

        $products = Product::whereIn('name', $externalNames)->get();

        return $products;
    }

    /**
     * @param array $externalProducts
     * @return array
     */
    protected function createBatchData(array $externalProducts): BatchData
    {
        $batchData = new BatchData;
        foreach ($externalProducts as $externalProduct)
            $batchData->add($externalProduct);

        return $batchData;
    }

    /**
     * Eltávolítja az invalid termékeket és egy külön collectionben visszaadja azokat
     * @param array $batchData
     * @return array
     */
    protected function validateBatchData(BatchData $batchData): array
    {
        $validator = Validator::make($batchData->toArray(), [
            'names' => new UniqueProductBatch
        ]);

        $failedNames = collect([]);
        if ($validator->fails()) {
            $failedNames = collect($validator->errors()->messages()['names']);

            foreach ($batchData->names as $i => $name) {
                if ($failedNames->contains($name)) {
                    $batchData->removeAt($i);
                }
            }
        }

        return [$batchData, $failedNames];
    }
}

class BatchData
{
    public $names           = [];
    public $descriptions    = [];
    public $slugs           = [];
    public $links           = [];

    public function add(array $externalProduct)
    {
        $this->names[] = $externalProduct['name'];
        $this->descriptions[] = $externalProduct['description'];
        $this->slugs[] = Str::slug($externalProduct['name']);
        $this->links[] = Arr::get($externalProduct, 'link');
    }

    public function removeAt(int $index)
    {
        unset($this->names[$index]);
        unset($this->descriptions[$index]);
        unset($this->slugs[$index]);
        unset($this->links[$index]);
    }

    public function toArray()
    {
        return [
            'names'         => $this->names,
            'descriptions'  => $this->descriptions,
            'slugs'         => $this->slugs,
            'links'         => $this->links,
        ];
    }
}

class BatchResult
{
    public $products;
    public $invalidNames;

    public function __construct($products, $invalidNames)
    {
        $this->products = $products;
        $this->invalidNames = $invalidNames;
    }
}
