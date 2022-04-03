<?php

namespace Tests\Feature;

use App\PageLoad;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProposerDeleteTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
    }

    /** @test */
    public function not_owner_cannot_delete()
    {
        $owner = create('User');
        $proposer = create('Proposer', 1, [
            'user_id'   => $owner->id
        ]);

        $other = create('User');
        $this->signIn($other);

        $response = $this->delete($proposer->path());
        $response->assertStatus(403);

        $this->assertDatabaseHas('proposers', [
            'id'  => $proposer->id
        ]);
    }

    /** @test */
    public function owner_can_delete()
    {
        $owner = create('User');
        $proposer = create('Proposer', 1, [
            'user_id'   => $owner->id
        ]);

        $this->signIn($owner);

        $response = $this->delete($proposer->path());
        $response->assertStatus(302);

        $this->assertDatabaseMissing('proposers', [
            'id'  => $proposer->id
        ]);
    }
}
