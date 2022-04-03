<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ProcessPageLoad;
use App\PageLoad;
use App\Partner;
use App\Segment;
use App\SegmentProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SegmentProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return PageLoad[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return PageLoad::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productId'     => 'required|exists:products,id',
            'priorityId'    => 'required|exists:segment_product_priorities,id',
            'segmentId'     => 'required|exists:segments,id'
        ]);

        if ($validator->fails())
            return response()
                ->json($validator->errors(), 422);

        try {
            $segmentProduct = new SegmentProduct;

            $segmentProduct->product_id     = request()->productId;
            $segmentProduct->priority_id    = request()->priorityId;
            $segmentProduct->segment_id     = request()->segmentId;
            $segmentProduct->user_id        = auth()->id();

            $segmentProduct->save();

        } catch(\Exception $e) {
            Log::error($e);
            return response()->json('Something went wrong', 500);
        }

        return response()->json($segmentProduct->load('product')->load('priority'));
    }

    public function destroy(SegmentProduct $segmentProduct)
    {
        $this->authorize('update', $segmentProduct->segment);
        $segmentProduct->delete();

        return response()->json('ok');
    }
}
