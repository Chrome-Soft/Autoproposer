<?php

namespace App\Jobs;

use App\SearchTerm;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessSearchTerm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    /**
     * @var SearchTerm
     */
    private $searchTerm;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SearchTerm $searchTerm)
    {
        //
        $this->searchTerm = $searchTerm;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->searchTerm->save();
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
        Log::error($this->searchTerm->toJson());
        Log::error($exception);
    }
}
