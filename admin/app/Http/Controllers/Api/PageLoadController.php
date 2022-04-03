<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ProcessPageLoad;
use App\PageLoad;
use App\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PageLoadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return PageLoad[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index(Request $request)
    {
        return PageLoad::where('from_url', 'LIKE', "%{$request->q}%")
            ->orWhere('to_url', 'LIKE', "%{$request->q}%")
            ->get()
            ->map(function ($x) use ($request) {
                $x['value'] = Str::contains($x->from_url, $request->q)
                    ? $x->from_url
                    : $x->to_url;
                return $x;
            })
            ->unique(function ($x) { return $x->value; })
            ->values()->all();
    }

    /**
     * @OA\Post(
     *      path="/page-load",
     *      operationId="store",
     *      tags={"PageLoad"},
     *      summary="Create new page load",
     *      @OA\Parameter(ref="#/components/parameters/X-Authorization"),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/PageLoad"),
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
            'cookieId'  => 'required',
            'partnerId' => 'required|exists:partners,external_id',
            'toUrl'     => 'required'
        ]);

        if ($validator->fails())
            return response()
                ->json($validator->errors(), 422);

        try {
            $partner = Partner::where('external_id', $request->partnerId)->first();
            $toUrl = $this->sanitizeUrl($partner, $request->toUrl);

            $pageLoad                       = new PageLoad;
            $pageLoad->from_url             = $this->sanitizeUrl($partner, $request->fromUrl);
            $pageLoad->to_url               = $toUrl ?? '/';
            $pageLoad->cookie_id            = $request->cookieId;
            $pageLoad->partner_external_id  = $request->partnerId;

            ProcessPageLoad::dispatch($pageLoad);
        } catch(\Exception $e) {
            Log::error($e);
            return response()->json('Something went wrong', 500);
        }

        return response()->json();
    }

    public function sanitizeUrl(Partner $partner, string $url = null)
    {
        $baseUrl = $this->getUrlWithoutQuery($url);
        return $partner->getTrackableUrl($baseUrl);
    }

    protected function getUrlWithoutQuery(string $url = null)
    {
        if (($pos = strpos($url, '?')) !== false) {
            return substr($url, 0, $pos);
        }

        return $url;
    }
}
