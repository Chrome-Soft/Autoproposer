<?php

namespace App\Console\Commands;

use App\Jobs\SegmentifyChunk;
use App\Jobs\Unsegmentify;
use App\Segment;
use App\UserData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Segmentify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'segment:segmentify {segment? : Segment slug for re-segmentify only this segment} {--onlyEmpty : If true, only user_data with NULL segment_id will be re-segmentified}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '
        Add segment id to all empty user data. If segment arg given, then it will unsegmentify 
        and segmentify all user data for this segment only. If onlyEmpty arg is true, then it will only segmentify user data 
        with NULL segment_id. If it is false, then it will segmentify all user_data with NULL or default segment_id.
        Both arguments cannot be used at the same time.';

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
    public function handle()
    {
        $defaultSegment = Segment::where('is_default', 1)->first();
        $segmentSlug = $this->argument('segment');
        $onlyEmpty = $this->option('onlyEmpty');

        if ($segmentSlug) {
            return $this->segmentifyByQuery($segmentSlug);
        }

        $query = UserData::select('id')
            ->whereNull('segment_id');

        $query = $onlyEmpty ? $query : $query->orWhere('segment_id', $defaultSegment->id);

        $ids = $query
            ->pluck('id')
            ->all();

        $chunks = array_chunk($ids, 50);

        foreach ($chunks as $chunk) {
            SegmentifyChunk::dispatch($chunk);
        }
    }

    /**
     * Szegmens létrehozáskoar fut le. Az 'Egyéb' szegmensből kiszedi azokat, amik az újonnan léltrehozott szegmensbe
     * tartoznak és átállítja őket
     */
    protected function segmentifyByQuery(string $slug)
    {
        $segment = Segment::where('slug', $slug)->first();
        $defaultSegment = Segment::where('is_default', 1)->first();

        $query = $segment->buildQuery();
        $items = $query->get();

        $ids = collect($items)
            ->whereIn('segment_id', [null, $defaultSegment->id, $segment->id])
            ->pluck('id')
            ->all();

        $chunks = array_chunk($ids, 50);

        $segment->unsegmentify();
        foreach ($chunks as $chunk) {
            SegmentifyChunk::dispatch($chunk, $segment);
        }

//        Unsegmentify::withChain([
//            new SegmentifyChunk($ids, $segment)
//        ])->dispatch($segment);
    }
}
