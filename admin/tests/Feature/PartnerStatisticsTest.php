<?php

namespace Tests\Feature;

use App\Interaction;
use App\PageLoad;
use App\Partner;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartnerStatisticsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_user_data_count()
    {
        $partner1 = create('Partner');
        $partner2 = create('Partner');

        $x = create('UserData', 10, [
            'partner_external_id'   => $partner1->external_id
        ]);
        create('UserData', 7, [
            'partner_external_id'   => $partner2->external_id
        ]);

        $stats = Partner::getUserDataStatistics();
        $partner1Stat = collect($stats)->where('external_id', $partner1->external_id)->first();
        $partner2Stat = collect($stats)->where('external_id', $partner2->external_id)->first();

        $this->assertEquals(10, $partner1Stat['user_data_count']);
        $this->assertEquals(7, $partner2Stat['user_data_count']);
    }

    /** @test */
    public function it_returns_page_load_count()
    {
        $partner1 = create('Partner');
        $partner2 = create('Partner');

        $x = create('PageLoad', 30, [
            'partner_external_id'   => $partner1->external_id
        ]);
        create('PageLoad', 21, [
            'partner_external_id'   => $partner2->external_id
        ]);

        $stats = Partner::getUserDataStatistics();
        $partner1Stat = collect($stats)->where('external_id', $partner1->external_id)->first();
        $partner2Stat = collect($stats)->where('external_id', $partner2->external_id)->first();

        $this->assertEquals(30, $partner1Stat['page_load_count']);
        $this->assertEquals(21, $partner2Stat['page_load_count']);
    }
}
