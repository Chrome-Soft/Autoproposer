<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace Tests\Unit;

use App\Product;
use App\Services\ProductAttributeSyncer;
use Tests\TestCase;

class ProductAttributeSyncerTest extends TestCase
{
    /** @test */
    public function grouping_simple_attributes()
    {
        $syncer = new ProductAttributeSyncer(new Product, [1,2], ['simple1', 'simple2']);
        $output = $this->invokeMethod($syncer, 'groupAttributeValuesByIds', [[]]);

        $this->assertEquals([
            1 => 'simple1',
            2 => 'simple2'
        ], $output);
    }

    /** @test */
    public function grouping_with_one_extra_attribute()
    {
        $syncer = new ProductAttributeSyncer(new Product, [1,2,3], ['simple1', 'extra1', 'extra2', 'simple2']);
        $output = $this->invokeMethod($syncer, 'groupAttributeValuesByIds', [[1]]);

        $this->assertEquals([
            1 => 'simple1',
            2 => ['extra1', 'extra2'],
            3 => 'simple2'
        ], $output);
    }

    /** @test */
    public function grouping_with_multiple_extra_attribute()
    {
        $syncer = new ProductAttributeSyncer(new Product, [1,2,3], ['simple1', 'extra1', 'extra2', 'extra3', 'extra4']);
        $output = $this->invokeMethod($syncer, 'groupAttributeValuesByIds', [[1,2]]);

        $this->assertEquals([
            1 => 'simple1',
            2 => ['extra1', 'extra2'],
            3 => ['extra3', 'extra4'],
        ], $output);
    }

    /** @test */
    public function grouping_only_extra_attributes()
    {
        $syncer = new ProductAttributeSyncer(new Product, [1,2,3], ['extra1', 'extra2', 'extra3', 'extra4', 'extra5', 'extra6']);
        $output = $this->invokeMethod($syncer, 'groupAttributeValuesByIds', [[0,1,2]]);

        $this->assertEquals([
            1 => ['extra1', 'extra2'],
            2 => ['extra3', 'extra4'],
            3 => ['extra5', 'extra6']
        ], $output);
    }

    /** @test */
    public function find_extra_attribute_indices_all_simple()
    {
        $syncer = new ProductAttributeSyncer(new Product, [1,2], []);
        $extraAttrs = collect([]);
        $output = $this->invokeMethod($syncer, 'findExtraAttributeIndicesIn', [$extraAttrs]);

        $this->assertEquals([], $output);
    }

    /** @test */
    public function find_extra_attribute_indices_has_some()
    {
        $syncer = new ProductAttributeSyncer(new Product, [1,2,3,4], []);
        $extraAttrs = collect([
            (object)[
                'id'    => 1
            ],
            (object)[
                'id'    => 4
            ]
        ]);
        $output = $this->invokeMethod($syncer, 'findExtraAttributeIndicesIn', [$extraAttrs]);

        $this->assertEquals([0,3], $output);
    }

    /** @test */
    public function it_returns_extra_indices()
    {
        $obj1 = new \stdClass();
        $obj1->id = 1;
        $obj2 = new \stdClass();
        $obj2->id = 15;

        $attrs = collect([$obj1, $obj2]);
        $syncer = new ProductAttributeSyncer(new Product, [1,2,3,4], []);

        $indices = $this->invokeMethod($syncer, 'findExtraAttributeIndicesIn', [$attrs]);

        // Id = 1 található a 0. indexben
        $this->assertCount(1, $indices);
        $this->assertEquals(0, $indices[0]);
    }
}
