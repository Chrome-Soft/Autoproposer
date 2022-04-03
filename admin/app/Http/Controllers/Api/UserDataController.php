<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ProcessSearchTerm;
use App\Jobs\ProcessUserData;
use App\PageLoad;
use App\SearchTerm;
use App\Services\GeoLocationService;
use App\Services\Recommender\IRecommenderService;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserDataController extends Controller
{
    /**
     * @var GeoLocationService
     */
    private $locationService;
    /**
     * @var IRecommenderService
     */
    private $recommenderService;

    public function __construct(GeoLocationService $locationService, IRecommenderService $recommenderService)
    {
        $this->locationService = $locationService;
        $this->recommenderService = $recommenderService;
    }

    public function index()
    {
        return response(UserData::all());
    }

    /**
     * @OA\Post(
     *      path="/user-data",
     *      operationId="store",
     *      tags={"UserData"},
     *      summary="Create user data",
     *      @OA\Parameter(ref="#/components/parameters/X-Authorization"),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UserData"),
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
        // Itt muszaj cookie_id -ként kapni, a unique validacio miatt
        $validator = Validator::make($request->all(), [
            'cookie_id'             => 'required|unique:user_data',
            'connection.ipAddress'  => 'required|ip',
            'partnerId'             => 'required|exists:partners,external_id'
        ]);

        if ($validator->fails())
            return response()
                ->json($validator->errors(), 422);

        $userData = new UserData;
        $userData->device_manufacturer       = either(Arr::get($request->device, 'manufacturer'), '');
        $userData->device_product            = either(Arr::get($request->device, 'product'), '');
        $userData->device_is_mobile          = Arr::get($request->device, 'isMobile', 0);
        $userData->device_memory             = Arr::get($request->device, 'memory', 0);
        $userData->device_screen_width       = either(Arr::get($request->device, 'resolution.width'), '');
        $userData->device_screen_height      = either(Arr::get($request->device, 'resolution.height'), '');

        $userData->os_architecture           = either(Arr::get($request->os, 'architecture'), '');
        $userData->os_name                   = either(Arr::get($request->os, 'family'), '');
        $userData->os_version                = either(Arr::get($request->os, 'version'), '');

        $userData->browser_name              = either(Arr::get($request->browser, 'name'), '');
        $userData->browser_version           = either(Arr::get($request->browser, 'version'), '');
        $userData->browser_user_agent        = either(Arr::get($request->browser, 'userAgent'), '');
        $userData->browser_language          = either(Arr::get($request->browser, 'language'), '');

        $userData->connection_bandwidth      = $this->getBandwidth(Arr::get($request->connection, 'bandwidth', 0));
        $userData->connection_ip_address     = either(Arr::get($request->connection, 'ipAddress'), '');
        $userData->connection_effective_type = either(Arr::get($request->connection, 'effectiveType'), '');

        $userData->timezone_offset           = $request->timezoneOffsetToUTC;
        $userData->cookie_id                 = $request->cookie_id;
        $userData->partner_external_id       = $request->partnerId;

        try {
            try {
                $locationData = $this->locationService->getLocationData($request->connection['ipAddress'], $request->location);
                $userData->fill($locationData);
            } catch (\GeoIp2\Exception\AddressNotFoundException | \InvalidArgumentException $ipEx) {
                // it's okay
            }

            ProcessUserData::dispatch($userData);
        } catch (\MaxMind\Db\Reader\InvalidDatabaseException $geoDbEx) {
            Log::error($geoDbEx);
            return response()->json('Error while reading geolocation database', 500);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json('Something went wrong', 500);
        }

        return response()->json();
    }

    public function getBandwidth($bandwidth)
    {
        $rounded = is_infinite($bandwidth) ? 0 : round($bandwidth, 1);

        $str = strval($bandwidth);

        /**
         * Kliensről köhetnek hasonló értékek:
         * 1.79769313486234564564654564654654654561321234987987654321354645132E+405
         * 1.7976931348623156e+305
         *
         * Az első végtelennek számít, nem tudja kezelni se string se round() függvény, ezért lesz 0
         * A másik példát stringként lehet még kezelni
         */
        if (Str::contains($str, '.') && strlen($str) > 5) {
            return round(substr($str, 0, 5), 1);
        }

        return $rounded;
    }

    /**
     * @OA\Patch(
     *      path="/user-data/register",
     *      operationId="store",
     *      tags={"UserData"},
     *      summary="Register user",
     *      @OA\Parameter(ref="#/components/parameters/X-Authorization"),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UserRegister"),
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
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cookieId'      => 'required',
            'partnerId'     => 'required|exists:partners,external_id',
            'userId'        => 'required',
//            'birthDate'     => 'sometimes|date_format:Y-m-d',
//            'phoneProvider' => 'sometimes|in:20,30,70',
//            'sex'           => 'sometimes|in:male,female'
        ]);

        if ($validator->fails())
            return response()
                ->json($validator->errors(), 422);

        try {
            $userData = UserData::where('cookie_id', $request->cookieId)->firstOrFail();

            $userData->sex = $request->sex;
            $userData->user_id = $request->userId;
            $userData->birth_date = $request->birthDate;
            $userData->email_domain = $request->emailDomain;
            $userData->phone_provider = $request->phoneProvider;
            $userData->location_real_city_name = $request->realCityName;
            $userData->location_real_postal_code = $request->realPostalCode;

            $userData->save();

            PageLoad::updateUserId($request->userId, $request->cookieId);

            return response()->json();
        } catch (ModelNotFoundException $mex) {
            Log::error($mex);
            return response()->json('Cookie id not found: ' . $request->cookieId, 404);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json('Something went wrong', 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/user-data/search-term",
     *      operationId="search-terms",
     *      tags={"UserData"},
     *      summary="Log search term",
     *      @OA\Parameter(ref="#/components/parameters/X-Authorization"),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/SearchTerm"),
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
    public function storeSearchTerm()
    {
        $validator = Validator::make(request()->all(), [
            'cookie_id'         => 'required',
            'partnerId'         => 'required|exists:partners,external_id',
            'searchTerm'        => 'required'
        ]);

        if ($validator->fails())
            return response()
                ->json($validator->errors(), 422);

        try {
            $searchTerm = new SearchTerm;
            $searchTerm->cookie_id                 = request()->cookie_id;
            $searchTerm->partner_external_id       = request()->partnerId;
            $searchTerm->search_term               = request()->searchTerm;

            ProcessSearchTerm::dispatch($searchTerm);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json('Something went wrong', 500);
        }

        return response()->json();
    }

    /**
     * @OA\Get(
     *      path="/user-data/csv",
     *      operationId="csv",
     *      tags={"UserData"},
     *      summary="Get all user data as csv",
     *      @OA\Parameter(ref="#/components/parameters/X-Authorization"),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=429, description="Too many requests"),
     *      @OA\Response(response=500, description="Failed operation")
     *  )
     */
    public function getCsv()
    {
        return \response()->download(storage_path('app/user_data.csv'));
    }
}
