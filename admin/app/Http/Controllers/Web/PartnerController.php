<?php

namespace App\Http\Controllers\Web;

use App\Model;
use App\Partner;
use App\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $deleted = $request->query('deleted');
        $relation = $deleted == 1 ? Relation::NOT_EMPTY : Relation::EMPTY;

        return view('partners.index')
            ->with('defaultFilter', ['column' => 'deleted_at', 'relation' => $relation])
            ->with('customActions', ['restore' => ['label' => 'Aktiválás', 'style' => 'success']])
            ->with('deleted', $deleted);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('partners.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        return $this->storeBase(function (array $values) {
            return auth()->user()->partners()->create(array_merge($values, [
                'external_id'           => Uuid::uuid4()->toString(),
                'is_anonymus_domain'    => $this->getIsAnonym()
            ]));
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function show(Partner $partner)
    {
        return view('partners.show', compact('partner'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function edit(Partner $partner)
    {
        return view('partners.edit', compact('partner'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function update(Partner $partner)
    {
        return $this->updateBase($partner, function (array $values) {
            return array_merge($values, [
                'is_anonymus_domain'    => $this->getIsAnonym()
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner $partner)
    {
        $this->authorize('delete', $partner);
        $partner->delete();

        return redirect('/partners')
            ->with('flash', 'Sikeres inaktiválás');
    }

    protected function validateRequest(Model $partner = null)
    {
        $url = 'required|';
        $url .= $partner ? 'unique:partners,url,' . $partner->id : 'unique:partners,url';

        return \request()->validate([
            'name'  => ['required'],
            'url'   => $url
        ]);
    }

    protected function getIsAnonym()
    {
        return \request()->is_anonymus_domain == 'on' ? true : false;
    }
}
