<?php

namespace Tests\Feature;

use App\Console\Kernel;
use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttributeType;
use App\ProductProductAttribute;
use App\Segment;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\UserData;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SegmentCrudTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'CriteriaTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'RelationTableSeeder']);
    }

    /** @test */
    public function create_segment()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $kernelMock = $this->createMock(Kernel::class);
        $kernelMock
            ->expects($this->once())
            ->method('call')
            ->with('segment:segmentify', ['segment' => 'segment1'])
            ->willReturn(null);

        $this->app->instance(Kernel::class, $kernelMock);

        $data = [
            'name' => 'segment1',
            'description'   => 'first one',
            'groups' => [
                [
                    'bool_type' => 'AND',
                    'criterias' => [
                        [ 'criteria' => 10, 'relation' => 1, 'value' => 'Chrome', 'bool_type' => 'OR' ],
                        [ 'criteria' => 10, 'relation' => 1, 'value' => 'Safari', 'bool_type' => 'OR' ],
                        [ 'criteria' => 16, 'relation' => 1, 'value' => 'Mac' ],
                    ]
                ],
                [
                    'criterias' => [
                        [ 'criteria' => 22, 'relation' => 1, 'value' => 'Szhely', 'bool_type' => 'AND' ],
                        [ 'criteria' => 18, 'relation' => 4, 'value' => '10' ]
                    ]
                ]
            ]
        ];

        $response = $this->post('/api/segments', $data);

        $response->assertStatus(200);
        $seg = json_decode($response->content());
        $id = (int)$seg->id;

        $this->assertDatabaseHas('segments', [
            'name'  => 'segment1'
        ]);

        $segment = Segment::where('id', $id)->first();

        $this->assertCount(2, $segment->groups);
        $this->assertCount(5, SegmentGroupCriteria::all());
    }

    /** @test */
    public function cannot_create_without_groups()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $data = [
            'name' => 'segment1',
            'description'   => 'first one',
            'groups' => []
        ];

        $response = $this->post('/api/segments', $data);
        $response->assertStatus(422);
    }

    /** @test */
    public function cannot_create_without_value()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $data = [
            'name' => 'segment1',
            'description'   => 'first one',
            'groups' => [
                [
                    'criterias' => [
                        [ 'criteria' => 22, 'relation' => 1],
                    ]
                ]
            ]
        ];

        $response = $this->post('/api/segments', $data);
        $response->assertStatus(422);
    }

    /** @test */
    public function it_calls_segmentify_when_updating()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $segment = create('Segment', 1, ['name' => 'segment1']);

        $kernelMock = $this->createMock(Kernel::class);
        $kernelMock->method('call')
            ->with('segment:segmentify', ['segment' => 'segment1'])
            ->willReturn(null);

        $this->app->instance(Kernel::class, $kernelMock);

        $data = $segment->toArray();
        $data['groups'] = [
            [
                'criterias' => [
                    [ 'criteria' => 10, 'relation' => 1, 'value' => 'Chrome', 'bool_type' => 'OR' ],
                    [ 'criteria' => 10, 'relation' => 1, 'value' => 'Safari', 'bool_type' => 'OR' ],
                    [ 'criteria' => 16, 'relation' => 1, 'value' => 'Mac' ],
                ]
            ]
        ];

        $response = $this->patch('/api/segments/' . $segment->slug, $data);
        $response->assertStatus(200);
    }

    /** @test */
    public function it_calls_segmentify_when_deleting()
    {
        $this->signIn();
        $segment = create('Segment', 1, ['name' => 'segment1', 'user_id' => $this->signedInUser->id]);

        $kernelMock = $this->createMock(Kernel::class);
        $kernelMock->method('call')
            ->with('segment:segmentify', ['segment' => 'segment1'])
            ->willReturn(null);

        $this->app->instance(Kernel::class, $kernelMock);

        $response = $this->delete('/segments/' . $segment->slug);
        $response->assertStatus(302);

        $segment = $segment->fresh();
        $this->assertNotNull($segment->deleted_at);
    }

    /** @test */
    public function it_calls_segmentify_when_restoring()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $segment = create('Segment', 1, ['name' => 'segment1', 'user_id' => $this->signedInUser->id, 'deleted_at' => Carbon::now()]);

        $kernelMock = $this->createMock(Kernel::class);
        $kernelMock->method('call')
            ->with('segment:segmentify', ['segment' => 'segment1'])
            ->willReturn(null);

        $this->app->instance(Kernel::class, $kernelMock);

        $response = $this->patch('/api/segments/' . $segment->slug . '/restore');
        $response->assertStatus(200);

        $segment = $segment->fresh();
        $this->assertNull($segment->deleted_at);
    }

    /** @test */
    public function cannot_create_identical_segments()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $kernelMock = $this->createMock(Kernel::class);
        $kernelMock->method('call')
            ->with('segment:segmentify', ['segment' => 'segment1'])
            ->willReturn(null);

        $this->app->instance(Kernel::class, $kernelMock);

        $data = [
            'name' => 'segment1',
            'description'   => 'first one',
            'groups' => [
                [
                    'criterias' => [
                        [ 'criteria' => 10, 'relation' => 1, 'value' => 'Chrome' ],
                    ]
                ],
            ]
        ];

        $response = $this->post('/api/segments', $data);
        $response->assertStatus(200);

        $data['name'] = 'identical segment';
        $response = $this->post('/api/segments', $data);
        $response->assertStatus(422);

        $this->assertDatabaseMissing('segments', [
            'name'  => $data['name']
        ]);
    }

    /** @test */
    public function cannot_update_segment_if_identical_one_exists()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $segment = new Segment;
        $segment->name = 'segment1';
        $segment->save();

        $group = new SegmentGroup;
        $group->segment_id = $segment->id;
        $group->bool_type = null;
        $group->save();

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = 10;
        $criteria->relation_id = 1;
        $criteria->value = 'Chrome';
        $criteria->bool_type = null;
        $criteria->save();

        $segment2 = new Segment;
        $segment2->name = 'segment2';
        $segment2->save();

        $group2 = new SegmentGroup;
        $group2->segment_id = $segment2->id;
        $group2->bool_type = null;
        $group2->save();

        $criteria2 = new SegmentGroupCriteria;
        $criteria2->segment_group_id = $group2->id;
        $criteria2->criteria_id = 10;
        $criteria2->relation_id = 1;
        $criteria2->value = 'Safari';
        $criteria2->bool_type = null;
        $criteria2->save();

        // update second one make exactly like first one
        $data = [
            'name' => 'segment2',
            'description'   => 'second one',
            'groups' => [
                [
                    'bool_type' => null,
                    'criterias' => [
                        [ 'criteria' => 10, 'relation' => 1, 'value' => 'Chrome', 'bool_type' => null ],
                    ]
                ],
            ]
        ];

        $response = $this->patch('/api/segments/' . $segment2->slug, $data);
        $response->assertStatus(422);

        $this->assertDatabaseHas('segments', [
            'name' => $data['name']
        ]);
    }

    /** @test */
    public function it_returns_0_as_max_sequence_if_no_segments()
    {
        Segment::truncate();

        $sequence = Segment::nextSequence();
        $this->assertEquals(0, $sequence);
    }

    /** @test */
    public function it_returns_the_next_sequence()
    {
        Segment::truncate();

        create('Segment', 1, ['sequence' => 0]);
        create('Segment', 1, ['sequence' => 1]);
        create('Segment', 1, ['sequence' => 2]);

        $sequence = Segment::nextSequence();
        $this->assertEquals(3, $sequence);
    }

    /** @test */
    public function it_fills_out_sequence_with_next_nmber()
    {
        Segment::truncate();

        $segment = create('Segment');
        $segment1 = create('Segment');
        $segment2 = create('Segment');

        $this->assertEquals(0, $segment->sequence);
        $this->assertEquals(1, $segment1->sequence);
        $this->assertEquals(2, $segment2->sequence);
    }

    /** @test */
    public function it_does_not_modify_sequence_when_deleting()
    {
        Segment::truncate();

        $segment = create('Segment');
        $segment1 = create('Segment');
        $segment2 = create('Segment');

        $segment1->delete();

        $this->assertEquals(1, $segment1->sequence);

    }

    /** @test */
    public function it_keeps_the_old_sequence_when_restoring()
    {
        Segment::truncate();

        $segment = create('Segment');
        $segment1 = create('Segment');
        $segment2 = create('Segment');

        $segment1->delete();
        $segment1->restore();

        $this->assertEquals(1, $segment1->sequence);
    }

    /** @test */
    public function it_keeps_track_of_sequences()
    {
        Segment::truncate();

        $segment = create('Segment');
        $segment1 = create('Segment');
        $segment2 = create('Segment');
        $segment3 = create('Segment');

        $this->assertEquals(0, $segment->sequence);
        $this->assertEquals(1, $segment1->sequence);
        $this->assertEquals(2, $segment2->sequence);
        $this->assertEquals(3, $segment3->sequence);

        $segment1->delete();

        $this->assertEquals(0, $segment->sequence);
        $this->assertEquals(1, $segment1->sequence);
        $this->assertEquals(2, $segment2->sequence);
        $this->assertEquals(3, $segment3->sequence);

        $segment1->restore();

        $this->assertEquals(0, $segment->sequence);
        $this->assertEquals(1, $segment1->sequence);
        $this->assertEquals(2, $segment2->sequence);
        $this->assertEquals(3, $segment3->sequence);

        $segment4 = create('Segment');
        $this->assertEquals(4, $segment4->sequence);
    }

    /** @test */
    public function it_returns_the_next_sequence_via_api()
    {
        Segment::truncate();

        create('Segment');      // 0
        create('Segment');      // 1
        create('Segment');      // 2

        $response = $this->get('/api/segments/sequence');
        $response->assertStatus(200);

        $seq = json_decode($response->content());
        $this->assertEquals(3, $seq);
    }

    /** @test */
    public function create_segment_with_template()
    {
        $user = create('User');
        $this->actingAs($user, 'api');
        $template = create('SegmentAppearanceTemplate');

        $data = [
            'name' => 'segment1',
            'description'   => 'first one',
            'template_id' => $template->id,
            'groups' => [
                [
                    'criterias' => [
                        [ 'criteria' => 10, 'relation' => 1, 'value' => 'Chrome'],
                    ]
                ],
            ]
        ];

        $response = $this->post('/api/segments', $data);

        $response->assertStatus(200);
        $seg = json_decode($response->content());
        $id = (int)$seg->id;

        $this->assertDatabaseHas('segments', [
            'name'  => 'segment1',
            'template_id' => $template->id
        ]);
    }
}
