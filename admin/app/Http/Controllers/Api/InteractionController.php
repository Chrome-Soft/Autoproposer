<?php

namespace App\Http\Controllers\Api;

use App\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InteractionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Interaction[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Interaction::with('items')->get();
    }

    /**
     * @OA\Post(
     *      path="/interaction",
     *      operationId="store",
     *      tags={"Interaction"},
     *      summary="Create new interaction between user and item",
     *      @OA\Parameter(ref="#/components/parameters/X-Authorization"),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/Interaction"),
     *          ),
     *     ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=429, description="Too many requests"),
     *      @OA\Response(response=500, description="Failed operation")
     *  )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cookieId'          => 'required',
            'partnerId'         => 'required|exists:partners,external_id',
            'type'              => 'required|in:buy,view,present',
            'userId'            => 'required_if:type,==,buy',
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required',
//            'items.*.name'      => 'required',
            'items.*.qty'       => 'required_if:type,==,buy|required_with:items.*.unitPrice',
            'items.*.unitPrice' => 'required_if:type,==,buy|required_with:items.*.qty',
        ]);

        if ($validator->fails())
            return response()
                ->json($validator->errors(), 422);

        try {
            Interaction::storeWithItems($request->type, $request->cookieId, $request->items, $request->userId);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json('Something went wrong', 500);
        }

        return response()->json();
    }
}
