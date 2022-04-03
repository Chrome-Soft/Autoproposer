<?php

namespace Tests\Feature;

use App\ApiKey;
use App\Partner;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

class PartnerStoreTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    /** @test */
    public function create_with_anonymus_domain()
    {
        $this->signIn();

        $data = [
            'name'                  => 'Partner 1',
            'url'                   => 'partner.hu',
            'is_anonymus_domain'    => 'on'
        ];
        $response = $this->post('/partners', $data);
        $response->assertStatus(302);

        $partner = Partner::where('name', 'Partner 1')->first();
        $this->assertEquals(1, $partner->is_anonymus_domain);
    }

    /** @test */
    public function create_without_anonymus_domain()
    {
        $this->signIn();

        $data = [
            'name'                  => 'Partner 1',
            'url'                   => 'partner.hu'
        ];
        $response = $this->post('/partners', $data);
        $response->assertStatus(302);

        $partner = Partner::where('name', 'Partner 1')->first();
        $this->assertEquals(0, $partner->is_anonymus_domain);
    }

    /** @test */
    public function regression_it_shows_a_user_readable_page_if_not_authorized_to_update()
    {
        $this->markTestSkipped();
        $this->withExceptionHandling();
        $this->signIn();
        $otherUser = create('User');

        $partner = create('Partner', 1, ['user_id' => $otherUser->id]);

        $response = $this->patch("/partners/{$partner->slug}");
        $response->assertStatus(403);
        $response->assertSeeText('Nincs jogosultságod a kiválasztott művelethez');
    }
}
