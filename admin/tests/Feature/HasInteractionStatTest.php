<?php

namespace Tests\Feature;

use App\Criteria;
use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttributeType;
use App\ProductProductAttribute;
use App\ProposerItem;
use App\Relation;
use App\Segment;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasInteractionStatTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'ProposerItemTypeTableSeeder']);
    }

    /** @test */
    public function it_returns_all_presents_for_product()
    {
        $product = create('Product');
        $interaction = create('Interaction', 1, ['type' => 'present']);
        $item = create('InteractionItem', 1, ['interaction_id' => $interaction->id, 'item_id' => $product->id, 'item_type' => Product::class]);

        $this->assertEquals(1, $product->all_present);
        $this->assertEquals(0, $product->all_view);
    }

    /** @test */
    public function it_returns_all_views_for_product()
    {
        $product = create('Product');
        $interaction = create('Interaction', 1, ['type' => 'view']);
        $item = create('InteractionItem', 1, ['interaction_id' => $interaction->id, 'item_id' => $product->id, 'item_type' => Product::class]);

        $this->assertEquals(1, $product->all_view);
        $this->assertEquals(0, $product->all_present);
    }

    /** @test */
    public function it_returns_all_stats_for_product()
    {
        $product = create('Product');
        $interactionView = create('Interaction', 1, ['type' => 'view']);
        $interactionPresent = create('Interaction', 1, ['type' => 'present']);

        $itemView = create('InteractionItem', 2, ['interaction_id' => $interactionView->id, 'item_id' => $product->id, 'item_type' => Product::class]);
        $itemPresent = create('InteractionItem', 3, ['interaction_id' => $interactionPresent->id, 'item_id' => $product->id, 'item_type' => Product::class]);

        $this->assertEquals(2, $product->all_view);
        $this->assertEquals(3, $product->all_present);
        $this->assertEquals('67%', $product->view_ratio);
    }

    /** @test */
    public function it_returns_all_presents_for_proposer_item()
    {
        $proposerItem = create('ProposerItem');
        $interaction = create('Interaction', 1, ['type' => 'present']);
        $item = create('InteractionItem', 1, ['interaction_id' => $interaction->id, 'item_id' => $proposerItem->id, 'item_type' => ProposerItem::class]);

        $this->assertEquals(1, $proposerItem->all_present);
        $this->assertEquals(0, $proposerItem->all_view);
    }

    /** @test */
    public function it_returns_all_views_for_proposer_item()
    {
        $proposerItem = create('ProposerItem');
        $interaction = create('Interaction', 1, ['type' => 'view']);
        $item = create('InteractionItem', 1, ['interaction_id' => $interaction->id, 'item_id' => $proposerItem->id, 'item_type' => ProposerItem::class]);

        $this->assertEquals(1, $proposerItem->all_view);
        $this->assertEquals(0, $proposerItem->all_present);
    }

    /** @test */
    public function it_returns_all_stats_for_proposer_item()
    {
        $proposerItem = create('ProposerItem');
        $interactionView = create('Interaction', 1, ['type' => 'view']);
        $interactionPresent = create('Interaction', 1, ['type' => 'present']);

        $itemView = create('InteractionItem', 2, ['interaction_id' => $interactionView->id, 'item_id' => $proposerItem->id, 'item_type' => ProposerItem::class]);
        $itemPresent = create('InteractionItem', 3, ['interaction_id' => $interactionPresent->id, 'item_id' => $proposerItem->id, 'item_type' => ProposerItem::class]);

        $this->assertEquals(2, $proposerItem->all_view);
        $this->assertEquals(3, $proposerItem->all_present);
        $this->assertEquals('67%', $proposerItem->view_ratio);
    }

    /** @test */
    public function it_returns_zero_if_type_wrong()
    {
        $proposerItem = create('ProposerItem');
        $interaction = create('Interaction', 1, ['type' => 'view']);
        $item = create('InteractionItem', 1, ['interaction_id' => $interaction->id, 'item_id' => $proposerItem->id, 'item_type' => Product::class]);

        $this->assertEquals(0, $proposerItem->all_view);
        $this->assertEquals(0, $proposerItem->all_present);
        $this->assertEquals('0%', $proposerItem->view_ratio);
    }

    /** @test */
    public function it_returns_zero_if_everything_zero()
    {
        $proposerItem = create('ProposerItem');

        $this->assertEquals(0, $proposerItem->all_view);
        $this->assertEquals(0, $proposerItem->all_present);

        // Division by zero
        $this->assertEquals('0%', $proposerItem->view_ratio);
    }
}
