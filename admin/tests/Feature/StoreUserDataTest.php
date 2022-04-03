<?php

namespace Tests\Feature;

use App\PageLoad;
use App\Segment;
use App\Services\GeoLocationService;
use App\Services\Recommender\IRecommenderService;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreUserDataTest extends TestCase
{
    use DatabaseMigrations, WithoutMiddleware, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initSegments();
    }

    /** @test */
    public function partner_id_not_exists()
    {
        $partner = create('Partner' , 1, [
            'external_id'   =>  \Ramsey\Uuid\Uuid::uuid4()->toString()
        ]);

        $data = [
            'partnerId'     => 'asdf',      // <- invalid
            'cookie_id'     => 'asdf',
            'connection'    => [
                'ipAddress' => $this->faker->ipv4
            ]
        ];

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(422);

        $this->assertArrayHasKey('partnerId', json_decode($response->getContent(), true));
    }

    /** @test */
    public function cookie_id_already_exists()
    {
        $userData = create('UserData', 1, [
            'cookie_id' => $this->faker->sha1
        ]);
        $partner = create('Partner' , 1);

        $data = [
            'cookie_id'      => $userData->cookie_id,
            'partnerId'     => $partner->external_id,
            'connection'    => [
                'ipAddress' => $this->faker->ipv4
            ]
        ];

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(422);

        $this->assertArrayHasKey('cookie_id', json_decode($response->getContent(), true));
    }

    /** @test */
    public function ip_address_empty()
    {
        $partner = create('Partner' , 1);

        $data = [
            'cookie_id'     => $this->faker->sha1,
            'partnerId'     => $partner->external_id,
            'connection'    => []
        ];

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(422);

        $this->assertArrayHasKey('connection.ipAddress', json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_stores_user_data()
    {
        $partner = create('Partner' , 1);
        $cookieId = $this->faker->sha1;

        $data = [
            'cookie_id'     => $cookieId,
            'partnerId'     => $partner->external_id,
            'connection'    => [
                'ipAddress' => '171.15.84.22'
            ]
        ];

        $recommenderMock = $this->createMock(RecommenderService::class);
        $locationServiceMock = $this->createMock(GeoLocationService::class);
        $locationServiceMock->method('getLocationData')
            ->willReturn([]);

        $this->app->instance(GeoLocationService::class, $locationServiceMock);
        $this->app->instance(RecommenderService::class, $recommenderMock);

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_data', [
            'cookie_id'                 => $cookieId,
            'partner_external_id'       => $partner->external_id,
            'connection_ip_address'     => '171.15.84.22'
        ]);
    }

    /** @test */
    public function it_fills_out_location_data()
    {
        $partner = create('Partner' , 1);
        $cookieId = $this->faker->sha1;

        $data = [
            'cookie_id'     => $cookieId,
            'partnerId'     => $partner->external_id,
            'connection'    => [
                'ipAddress' => '171.15.84.22'
            ]
        ];

        $recommenderMock = $this->createMock(RecommenderService::class);

        $locationServiceMock = $this->createMock(GeoLocationService::class);
        $locationServiceMock->method('getLocationData')
            ->willReturn([
                'location_country_code'     => 'HU',
                'location_country_name'     => 'Hungary',
                'location_city_name'        => 'Min-den-ki szom-bat-helyi',
                'location_postal_code'      => '9700',
                'location_subdivision_name' => 'Vas',
                'location_subdivision_code' => 'Vas',
                'location_latitude'         => 1,
                'location_longitude'        => 2
            ]);

        $this->app->instance(GeoLocationService::class, $locationServiceMock);
        $this->app->instance(RecommenderService::class, $recommenderMock);

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_data', [
            'cookie_id'                 => $cookieId,
            'partner_external_id'       => $partner->external_id,
            'connection_ip_address'     => '171.15.84.22',

            'location_country_code'     => 'HU',
            'location_country_name'     => 'Hungary',
            'location_city_name'        => 'Min-den-ki szom-bat-helyi',
            'location_postal_code'      => '9700',
            'location_subdivision_name' => 'Vas',
            'location_subdivision_code' => 'Vas',
            'location_latitude'         => 1,
            'location_longitude'        => 2
        ]);
    }

    /** @test */
    public function it_fills_out_segment_id()
    {
        $partner = create('Partner' , 1);
        $cookieId = $this->faker->sha1;

        $data = [
            'cookie_id'     => $cookieId,
            'partnerId'     => $partner->external_id,
            'connection'    => [
                'ipAddress' => '171.15.84.22'
            ]
        ];

        $segment = create('Segment');

        $recommenderMock = $this->createMock(RecommenderService::class);
        $recommenderMock->method('segmentify')
            ->willReturn($segment);

        $locationServiceMock = $this->createMock(GeoLocationService::class);
        $locationServiceMock->method('getLocationData')
            ->willReturn([]);

        $this->app->instance(GeoLocationService::class, $locationServiceMock);
        $this->app->instance(IRecommenderService::class, $recommenderMock);

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_data', [
            'segment_id'                => $segment->id,
            'cookie_id'                 => $cookieId,
            'partner_external_id'       => $partner->external_id,
            'connection_ip_address'     => '171.15.84.22'
        ]);
    }

    /** @test */
    public function regression_test_store_infinite_bandwidth_as_zero()
    {
        $partner = create('Partner' , 1);
        $cookieId = $this->faker->sha1;

        $data = [
            'cookie_id'     => $cookieId,
            'partnerId'     => $partner->external_id,
            'connection'    => [
                'ipAddress' => '171.15.84.22',
                'bandwidth' => 1.79769313486234564564654564654654654561321234987987654321354645132E+405
            ]
        ];

        $recommenderMock = $this->createMock(RecommenderService::class);
        $locationServiceMock = $this->createMock(GeoLocationService::class);
        $locationServiceMock->method('getLocationData')
            ->willReturn([]);

        $this->app->instance(GeoLocationService::class, $locationServiceMock);
        $this->app->instance(RecommenderService::class, $recommenderMock);

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_data', [
            'cookie_id'                 => $cookieId,
            'partner_external_id'       => $partner->external_id,
            'connection_ip_address'     => '171.15.84.22',
            'connection_bandwidth'      => 0,       // infinite
        ]);
    }

    /** @test */
    public function regression_test_store_huge_but_not_infinite_bandwidth()
    {
        $partner = create('Partner' , 1);
        $cookieId = $this->faker->sha1;

        $data = [
            'cookie_id'     => $cookieId,
            'partnerId'     => $partner->external_id,
            'connection'    => [
                'ipAddress' => '171.15.84.22',
                'bandwidth' => 1.7976931348623156e+305
            ]
        ];

        $recommenderMock = $this->createMock(RecommenderService::class);
        $locationServiceMock = $this->createMock(GeoLocationService::class);
        $locationServiceMock->method('getLocationData')
            ->willReturn([]);

        $this->app->instance(GeoLocationService::class, $locationServiceMock);
        $this->app->instance(RecommenderService::class, $recommenderMock);

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_data', [
            'cookie_id'                 => $cookieId,
            'partner_external_id'       => $partner->external_id,
            'connection_ip_address'     => '171.15.84.22',
            'connection_bandwidth'      => 1.8,       // infinite
        ]);
    }

    /** @test */
    public function regression_test_format_bandwidth_decimals()
    {
        $partner = create('Partner' , 1);
        $cookieId = $this->faker->sha1;

        $data = [
            'cookie_id'     => $cookieId,
            'partnerId'     => $partner->external_id,
            'connection'    => [
                'ipAddress' => '171.15.84.22',
                'bandwidth' => 1.797693
            ]
        ];

        $recommenderMock = $this->createMock(RecommenderService::class);
        $locationServiceMock = $this->createMock(GeoLocationService::class);
        $locationServiceMock->method('getLocationData')
            ->willReturn([]);

        $this->app->instance(GeoLocationService::class, $locationServiceMock);
        $this->app->instance(RecommenderService::class, $recommenderMock);

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_data', [
            'cookie_id'                 => $cookieId,
            'partner_external_id'       => $partner->external_id,
            'connection_ip_address'     => '171.15.84.22',
            'connection_bandwidth'      => 1.8,       // format
        ]);
    }

    /** @test */
    public function regression_test_it_does_not_throw_error_if_ip_invalid()
    {
        $partner = create('Partner' , 1);
        $cookieId = $this->faker->sha1;

        $data = [
            'cookie_id'     => $cookieId,
            'partnerId'     => $partner->external_id,
            'connection'    => [
                'ipAddress' => '10.80.7.203'        // invalid
            ]
        ];

        $recommenderMock = $this->createMock(RecommenderService::class);
        $locationServiceMock = $this->createMock(GeoLocationService::class);
        $locationServiceMock->method('getLocationData')
            ->willReturn([]);

        $this->app->instance(GeoLocationService::class, $locationServiceMock);
        $this->app->instance(RecommenderService::class, $recommenderMock);

        $response = $this->post('/api/user-data', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_data', [
            'cookie_id'                 => $cookieId,
            'partner_external_id'       => $partner->external_id,
            'connection_ip_address'     => '10.80.7.203',
        ]);
    }

    protected function initSegments()
    {
        $segment = new Segment();
        $slug = 'holland-fiatalok';
        $segment->slug = $slug;
        $segment->name = $slug;
        $segment->save();

        $segment = new Segment();
        $slug = 'osztrak-kozepkoru-kozmetikai-turizmus';
        $segment->slug = $slug;
        $segment->name = $slug;
        $segment->save();

        $segment = new Segment();
        $slug = 'orosz-gyogyfurdoturizmus';
        $segment->slug = $slug;
        $segment->name = $slug;
        $segment->save();

        $segment = new Segment();
        $slug = 'egyszeru-angol-turista';
        $segment->slug = $slug;
        $segment->name = $slug;
        $segment->save();

        $segment = new Segment();
        $slug = 'nyugat-europai-ertelmisegi';
        $segment->slug = $slug;
        $segment->name = $slug;
        $segment->save();

        $segment = new Segment();
        $slug = 'hipszter-kultura';
        $segment->slug = $slug;
        $segment->name = $slug;
        $segment->save();

//        $segment = new Segment();
//        $slug = 'egyeb';
//        $segment->slug = $slug;
//        $segment->name = $slug;
//        $segment->save();
    }
}
