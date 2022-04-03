<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace Tests\Unit;

use App\Product;
use App\Services\ProductAttributeSyncer;
use App\Services\ProductImport\ProductImportStatistics;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ProductImportStatisticsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function one_product_one_error()
    {
        $products = make('Product', 3, ['user_id' => null]);
        $stat = new ProductImportStatistics;
        $stat->all($products->toArray());

        $stat->failed($products[0], new \Exception('Fatal'));
        $stat->createdWithError($products[1], new \Exception('Error'));
        $stat->passed($products[2]);

        $this->assertEquals(3, $stat->all);
        $this->assertEquals(1, $stat->createdWithError);
        $this->assertEquals(1, $stat->failed);
        $this->assertEquals(1, $stat->passed);

        $this->assertCount(2, $stat->failedProducts);
        $this->assertCount(1, $stat->failedProducts[$products[0]->slug]['errors']);
        $this->assertCount(1, $stat->failedProducts[$products[1]->slug]['errors']);
    }

    /** @test */
    public function one_product_multiple_errors()
    {
        $products = make('Product', 3, ['user_id' => null]);
        $stat = new ProductImportStatistics;
        $stat->all($products->toArray());

        $stat->failed($products[0], new \Exception('Fatal'));
        $stat->passed($products[2]);

        $stat->createdWithError($products[1], new \Exception('Error1'));
        $stat->createdWithError($products[1], new \Exception('Error2'));
        $stat->createdWithError($products[1], new \Exception('Error3'));

        $this->assertEquals(3, $stat->all);
        $this->assertEquals(1, $stat->createdWithError);
        $this->assertEquals(1, $stat->failed);
        $this->assertEquals(1, $stat->passed);

        $this->assertCount(2, $stat->failedProducts);
        $this->assertCount(1, $stat->failedProducts[$products[0]->slug]['errors']);

        $this->assertCount(3, $stat->failedProducts[$products[1]->slug]['errors']);
        $this->assertContains('Error1', $stat->failedProducts[$products[1]->slug]['errors'][0]);
        $this->assertContains('Error2', $stat->failedProducts[$products[1]->slug]['errors'][1]);
        $this->assertContains('Error3', $stat->failedProducts[$products[1]->slug]['errors'][2]);
    }
}
