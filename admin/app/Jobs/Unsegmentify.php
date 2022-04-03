<?php

namespace App\Jobs;

use App\Segment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class Unsegmentify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Segment
     */
    private $segment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Segment $segment)
    {
        //
        $this->segment = $segment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->segment->unsegmentify();
    }
}
