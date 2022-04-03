<?php

namespace Tests\Feature;

use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use ParseCsv\Csv;

class CreateUserDataCsvTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
    }

    /** @test */
    public function it_removes_old_and_creates_a_new_csv()
    {
        $partner = create('Partner');

        $userData = new UserData;
        $userData->device_is_mobile = false;
        $userData->created_at = date('Y-m-d');
        $userData->partner_external_id = $partner->external_id;
        $userData->cookie_id = 'cookie-id';
        $userData->save();

        $this->artisan('userdata:csv');
        Storage::shouldReceive('delete')->with('app/user_data.csv');
        Storage::shouldReceive('putAsFile')->with('app/user_data.csv');

        $path = storage_path('app/user_data.csv');

        $csv = new Csv;
        $csv->parse($path);

        $this->assertCount(1, $csv->data);

        unlink($path);
    }
}
