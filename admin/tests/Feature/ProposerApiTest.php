<?php

namespace Tests\Feature;

use App\PageLoad;
use App\Services\GeoLocationService;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProposerApiTest extends TestCase
{
    use DatabaseMigrations, WithoutMiddleware, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
    }

    /** @test */
    public function it_gets_proposer_by_page_url_with_slash()
    {
        $partner = create('Partner' , 1, ['external_id' => 1]);
        $proposer = create('Proposer', 1, [
            'page_url' => 'rolunk',
            'partner_id' => $partner->id]
        );

        $response = $this->post('/api/proposer/1', [
            'pageUrl'      => '/rolunk',
        ]);
        $response->assertStatus(200);

        $res = json_decode($response->getContent(), true);
        $this->assertEquals($proposer->id, $res['id']);
    }

    /** @test */
    public function it_gets_proposer_by_page_url_without_slash()
    {
        $partner = create('Partner' , 1, ['external_id' => 1]);
        $proposer = create('Proposer', 1, [
            'page_url' => 'rolunk',
            'partner_id' => $partner->id]
        );

        $response = $this->post('/api/proposer/1', [
            'pageUrl'      => 'rolunk',
        ]);
        $response->assertStatus(200);

        $res = json_decode($response->getContent(), true);
        $this->assertEquals($proposer->id, $res['id']);
    }
}
