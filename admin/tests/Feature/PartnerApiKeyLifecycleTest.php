<?php

namespace Tests\Feature;

use App\ApiKey;
use App\Partner;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Gate;

class PartnerApiKeyLifecycleTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    /** @test */
    public function create_partner()
    {
        $this->signIn();

        $data = [
            'name'  => 'Partner 1',
            'url'   => 'partner.hu'
        ];
        $response = $this->post('/partners', $data);
        $response->assertStatus(302);

        $partner = Partner::where('name', 'Partner 1')->first();

        $this->assertEquals('partner-1', $partner->slug);
        $this->assertEquals(auth()->id(), $partner->user_id);

        // API key created
        $this->assertNotEmpty($partner->apiKey);
        $this->assertEquals($partner->slug, $partner->apiKey->name);
    }

    /** @test */
    public function update_partner()
    {
        $this->signIn();

        $data = [
            'name'  => 'Partner 100',
            'url'   => 'partner100.hu'
        ];
        $response = $this->post('/partners', $data);
        $response->assertStatus(302);

        $partner = Partner::where('name', $data['name'])->first();

        $response = $this->patch($partner->path(), [
            'name'  => 'Partner 101',
            'url'   => 'partner101.hu'
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('partners', [
            'name'      => 'Partner 101',
            'slug'      => 'partner-101',
            'user_id'   => auth()->id()
        ]);

        // API key updated
        $this->assertNotEmpty($partner->apiKey);
        $this->assertEquals('partner-101', $partner->apiKey->name);
    }

    /** @test */
    public function delete_partner()
    {
        $this->signIn();

        $data = [
            'name'  => 'Partner 100',
            'url'   => 'partner100.hu'
        ];
        $response = $this->post('/partners', $data);
        $response->assertStatus(302);

        $partner = Partner::where('name', $data['name'])->first();
        $this->assertNotNull($partner->apiKey);

        $response = $this->delete($partner->path());
        $response->assertStatus(302);

        $partner = $partner->fresh();
        $this->assertNotNull($partner->deleted_at);

        $apiKey = ApiKey::onlyTrashed()->first();
        $this->assertNull($apiKey->partner_id);
        $this->assertNotNull($apiKey->deleted_at);
    }

    /** @test */
    public function restore_partner()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $data = [
            'name'  => 'Partner 100',
            'url'   => 'partner100.hu'
        ];
        $response = $this->post('/partners', $data);
        $response->assertStatus(302);

        $partner = Partner::where('name', $data['name'])->first();
        $oldApiKeyId = $partner->apiKey->id;

        $response = $this->delete($partner->path());
        $response->assertStatus(302);

        $partner = $partner->fresh();
        $response = $this->patch('/api' . $partner->path('restore'));
        $response->assertStatus(200);

        $partner = $partner->fresh();
        $this->assertNull($partner->deleted_at);
        $this->assertNotNull($partner->apiKey);

        $this->assertNotEquals($partner->apiKey->id, $oldApiKeyId);
    }
}
