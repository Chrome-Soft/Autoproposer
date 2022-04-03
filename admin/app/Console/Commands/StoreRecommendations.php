<?php

namespace App\Console\Commands;

use App\Segment;
use App\Services\Recommender\IRecommenderService;
use Illuminate\Console\Command;

class StoreRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recommendation:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store recommended products to all segments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(IRecommenderService $recommenderService)
    {
        $segmentIds = Segment::all()->pluck('id')->all();
        $recommenderService->storeRecommendations($segmentIds);
    }
}
