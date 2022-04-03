<?php

namespace Tests\Feature;

use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttributeType;
use App\ProductProductAttribute;
use App\Segment;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SegmentInteractionTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
    }

    /** @test */
    public function get_interactions()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $segment = create('Segment');
        $userData = create('UserData', 1, [
            'segment_id'    => $segment->id,
            'cookie_id'     => 'c_1234'
        ]);
        $interaction = create('Interaction', 1, [
            'type'          => 'buy',
            'cookie_id'     => $userData->cookie_id
        ]);
        $items = create('InteractionItem', 2, [
            'interaction_id'    => $interaction->id
        ]);
        $interactionOthers = create('Interaction', 2);

        $interactions = $segment->getInteractions();

        $this->assertCount(1, $interactions);
        $this->assertEquals('buy', $interactions[0]['type']);
        $this->assertCount(2, $interactions[0]['product_ids']);
    }
}
