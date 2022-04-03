<?php

namespace App\Jobs;

use App\Http\Controllers\Auth\LoginController;
use App\Segment;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SegmentifyChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var array
     */
    private $ids;
    /**
     * @var Segment
     */
    private $segment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $ids, Segment $segment = null)
    {
        $this->ids = $ids;
        $this->segment = $segment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RecommenderService $recommenderService)
    {
        if ($this->segment) {
            $this->segmentifyOneSegment();
            return;
        }

        $userDatas = UserData::whereIn('id', $this->ids)->get();
        foreach ($userDatas as $userData) {
            $userData->segment_id = $recommenderService->segmentify($userData);
            $userData->save();
        }
    }

    protected function segmentifyOneSegment()
    {
        DB::table('user_data')->whereIn('id', $this->ids)->update(['segment_id' => $this->segment->id]);
    }
}
