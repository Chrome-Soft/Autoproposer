<?php

namespace Tests\Feature;

use App\PageLoad;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterUserDataTest extends TestCase
{
    use DatabaseMigrations, WithoutMiddleware;

    /** @test */
    public function partner_id_not_exists()
    {
        $partner = create('Partner' , 1, [
            'external_id'   =>  \Ramsey\Uuid\Uuid::uuid4()->toString()
        ]);

        $data = [
            'partnerId'     => 'asdf',      // <- invalid
            'cookieId'      => 'asdf',
            'userId'        => 'asdf',
            'birthDate'     => '1990-05-21',
            'phoneProvider' => '70'
        ];

        $response = $this->patch('/api/user-data/register', $data);
        $response->assertStatus(422);
    }

    /** @test */
    public function cookie_id_empty()
    {
        $partner = create('Partner' , 1, [
            'external_id'   =>  \Ramsey\Uuid\Uuid::uuid4()->toString()
        ]);

        $data = [
            'partnerId'     => $partner->external_id,
            'userId'        => 'asdf',
            'birthDate'     => '1990-05-21',
            'phoneProvider' => '70'
        ];

        $response = $this->patch('/api/user-data/register', $data);
        $response->assertStatus(422);
    }

    /** @test */
    public function user_id_empty()
    {
        $partner = create('Partner' , 1, [
            'external_id'   =>  \Ramsey\Uuid\Uuid::uuid4()->toString()
        ]);

        $data = [
            'partnerId'     => $partner->external_id,
            'cookie_id'     => 'asdf',
            'birthDate'     => '1990-05-21',
            'phoneProvider' => '70'
        ];

        $response = $this->patch('/api/user-data/register', $data);
        $response->assertStatus(422);
    }

    /** @test */
    public function register()
    {
        $partner = create('Partner');

        $userData = create('UserData',1, [
            'user_id'               => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'partner_external_id'   => $partner->external_id
        ]);
        $pageLoadsHasUser = create('PageLoad', 2, [
            'user_id'               => $userData->user_id,
            'cookie_id'             => $userData->cookie_id,
            'partner_external_id'   => $partner->external_id
        ]);
        $pageLoadsNoUser = create('PageLoad', 2);

        $data = [
            'partnerId'     => $partner->external_id,
            'cookieId'      => $userData->cookie_id,
            'userId'        => $userData->user_id,
            'birthDate'     => '1990-05-21',
            'phoneProvider' => '70',
            'emailDomain'   => 'gmail.com',
            'sex'           => 'male'
        ];

        $response = $this->json('PATCH', '/api/user-data/register', $data);
        $response->assertStatus(200);

        $userData = $userData->fresh();

        $this->assertEquals($data['birthDate'], $userData->birth_date);
        $this->assertEquals($data['phoneProvider'], $userData->phone_provider);
        $this->assertEquals($data['emailDomain'], $userData->email_domain);
        $this->assertEquals($data['sex'], $userData->sex);

        foreach ($pageLoadsHasUser as $pageLoad)
            $this->assertEquals($userData->user_id, $pageLoad->user_id);

        foreach ($pageLoadsNoUser as $pageLoad)
            $this->assertEmpty($pageLoad->user_id);
    }
}
