<?php

namespace App\Jobs;

use App\PageLoad;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessPageLoad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    /**
     * @var PageLoad
     */
    private $pageLoad;

    /**
     * Create a new job instance.
     *
     * @param PageLoad $pageLoad
     */
    public function __construct(PageLoad $pageLoad)
    {
        $this->pageLoad = $pageLoad;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->pageLoad->save();
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error($exception);
    }
}
