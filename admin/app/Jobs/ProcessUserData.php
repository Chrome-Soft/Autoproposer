<?php

namespace App\Jobs;

use App\Services\Recommender\IRecommenderService;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessUserData implements ShouldQueue
{
    // FONTOS: Még el nem mentett modellek esetén nem lehet a SerializeModel traitet használni, mivel az id -t fog szerializálni, és alapján kérdez le
    use Dispatchable, InteractsWithQueue, Queueable;

    public $timeout = 600;

    /**
     * @var UserData
     */
    private $userData;

    /**
     * Create a new job instance.
     *
     * @param UserData $userData
     */
    public function __construct(UserData $userData)
    {
        $this->userData = $userData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IRecommenderService $recommenderService)
    {
        // Neurális háló. Demó alatt nem használjuk
        $this->userData->save();
        $segment = $recommenderService->segmentify($this->userData);
        $this->userData->segment_id = $segment->id;
        $this->userData->save();

        // Ez fontos, hogy ne a Controller -ben, hanem külön process -ben történjen. Időigényes lehet ha sok adat van
//        $this->userData->save();
//        $this->userData->segmentify();
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error('USER DATA STORE ERROR');
        Log::error($this->userData->toJson());
        Log::error($exception);
    }
}
