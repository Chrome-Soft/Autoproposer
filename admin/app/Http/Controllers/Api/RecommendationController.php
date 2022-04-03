<?php

namespace App\Http\Controllers\Api;

use App\Product;
use App\Proposer;
use App\Segment;
use App\SegmentProductPriority;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RecommendationController extends Controller
{
    /**
     * @var RecommenderService
     */
    protected $recommenderService;

    public function __construct(RecommenderService $recommenderService)
    {
        $this->recommenderService = $recommenderService;
    }

    public function index(string $cookieId, Proposer $proposer)
    {
        $segment = $this->recommenderService->waitForSegment($cookieId);
        if (!$segment) {
            $segment = Segment::where('is_default', 1)->first();
        }

        $segment->load('appearance_template');

        return response()->json([
            'items'     => $this->recommenderService->recommend($segment, $proposer),
            'segment'   => $segment
        ]);
    }
}
