<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\RecommendationController;
use App\Interaction;
use App\Model;
use App\Partner;
use App\Proposer;
use App\ProposerType;
use App\Segment;
use App\Services\Recommender\IRecommenderService;

class ProposerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('proposers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $partners = Partner::all();
        $types = ProposerType::getAllFromCache();
        return view('proposers.create', compact('partners', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        return $this->storeBase(function (array $values) {
            return Proposer::create(array_merge($values, [
                'description'   => \request()->description
            ]));
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Proposer $proposer
     * @return \Illuminate\Http\Response
     */
    public function show(Proposer $proposer)
    {
        $apiKey = $proposer->partner->apiKey->key;
        return view('proposers.show', compact('proposer', 'apiKey'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Proposer $proposer
     * @return \Illuminate\Http\Response
     */
    public function edit(Proposer $proposer)
    {
        $partners = Partner::all();
        $types = ProposerType::getAllFromCache();
        return view('proposers.edit', compact('proposer', 'partners', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Proposer $proposer
     * @return \Illuminate\Http\Response
     */
    public function update(Proposer $proposer)
    {
        return $this->updateBase($proposer, function (array $values) {
            return $values;
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Proposer $proposer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Proposer $proposer)
    {
        $this->authorize('delete', $proposer);
        $proposer->delete();

        return redirect('/proposers')
            ->with('flash', 'Sikeres törlés');
    }

    public function preview(Proposer $proposer, IRecommenderService $recommenderService)
    {
        $segment = Segment::where('is_default', 0)->first();
        $items = $recommenderService->recommend($segment, $proposer);
        $trackInteractions = false;
        $cookieId = null;

        return view('proposers.preview', compact('proposer', 'items', 'noNav', 'trackInteractions', 'cookieId'));
    }

    public function iframe(Proposer $proposer, $cookieId, IRecommenderService $recommenderService)
    {
        $apiKey = \request()->input('api_key');
        if ($proposer->partner->apiKey->key !== $apiKey) {
            abort(403);
        }

        $segment = $recommenderService->waitForSegment($cookieId);
        $items = $recommenderService->recommend($segment, $proposer);

        $interactionItems = $this->getInteractionItems($items, $cookieId);
        Interaction::storeWithItems('present', $cookieId, $interactionItems);

        $trackInteractions = true;

        return view('proposers.iframe', compact('proposer', 'items', 'trackInteractions', 'cookieId'));
    }

    protected function getInteractionItems(array $recommendedItems, $cookieId)
    {
        return collect($recommendedItems)->map(function($x) {
            return [
                'id'      => $x['id'],
                'name'    => $x['name'] ?? null,
                'type'    => $x['type_key']
            ];
        })->toArray();
    }

    protected function validateRequest(Model $model = null)
    {
        return \request()->validate([
            'name'              => ['required'],
            'type_id'           => ['required'],
            'width'             => ['required_if:type_id,==,1'],
            'height'            => ['required_if:type_id,==,1'],
            'page_url'          => ['required_if:type_id,==,2'],
            'max_item_number'   => ['required', 'integer', 'min:1', 'max:10'],
            'partner_id'        => ['required', 'exists:partners,id']
        ]);
    }
}
