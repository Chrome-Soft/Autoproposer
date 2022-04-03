<?php

namespace Tests\Feature;

use App\Console\Kernel;
use App\Services\Recommender\IRecommenderService;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use ParseCsv\Csv;

class TrainNeuralNetworkTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
    }

    /** @test */
    public function it_creates_user_data_csv_and_calls_recommender_service()
    {
        $recommenderMock = $this->createMock(RecommenderService::class);
        $recommenderMock->expects($this->once())
            ->method('trainNeuralNetwork');

        $kernelMock = $this->createMock(Kernel::class);
        $kernelMock->expects($this->once())
            ->method('call')
            ->with('userdata:csv');

        $this->app->instance(IRecommenderService::class, $recommenderMock);
        $this->app->instance(Kernel::class, $kernelMock);

        $this->artisan('neuralnetwork:train');
    }
}
