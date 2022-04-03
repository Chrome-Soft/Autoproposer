<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Console\Commands;


use App\Console\Kernel;
use App\Services\Recommender\IRecommenderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TrainNeuralNetwork extends Command
{
    protected $signature = 'neuralnetwork:train';
    protected $description = 'Train the network with all user_data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(IRecommenderService $recommenderService, Kernel $kernel)
    {
        $kernel->call('userdata:csv');
        $recommenderService->trainNeuralNetwork();
    }
}
