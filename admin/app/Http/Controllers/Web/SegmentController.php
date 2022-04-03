<?php

namespace App\Http\Controllers\Web;

use App\Criteria;
use App\Jobs\Unsegmentify;
use App\Model;
use App\Product;
use App\Relation;
use App\Segment;
use App\SegmentAppearanceTemplate;
use App\SegmentProductPriority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SegmentController extends Controller
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

        return view('segments.index')
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
        $data = $this->getFormData();
        return view('segments.create')
            ->with('relations', $data['relations'])
            ->with('criterias', $data['criterias'])
            ->with('availableRelationMap', $data['availableRelationMap'])
            ->with('appearanceTemplates', $data['appearanceTemplates']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Segment  $segment
     * @return \Illuminate\Http\Response
     */
    public function edit(Segment $segment)
    {
        $data = $this->getFormData();
        return view('segments.edit')
            ->with('segment', $segment)
            ->with('relations', $data['relations'])
            ->with('criterias', $data['criterias'])
            ->with('availableRelationMap', $data['availableRelationMap'])
            ->with('appearanceTemplates', $data['appearanceTemplates']);
    }

    protected function getFormData()
    {
        return [
            'relations' => Cache::get('relations'),
            'criterias' => Cache::get('criterias'),
            'availableRelationMap' => Criteria::getAvailableRelationMap(),
            'appearanceTemplates'   => Cache::get('segment_appearance_templates')
        ];
    }

    public function store()
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Segment  $segment
     * @return \Illuminate\Http\Response
     */
    public function show(Segment $segment)
    {

        $segmentProducts = $segment->segment_products()->with('product')->with('priority')->get();
        //$products = Product::getAllExcept($segment);

        return view('segments.show', compact('segment', 'segmentProducts'/*, 'products'*/));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Segment  $segment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Segment $segment)
    {
        $this->authorize('delete', $segment);
        Unsegmentify::dispatch($segment);
        $segment->delete();

        return redirect('/segments')
            ->with('flash', 'Sikeres inaktiválás');
    }

    protected function validateRequest(Model $model = null)
    {
        return \request()->validate([
            'name'  => ['required', 'unique:segments,name']
        ]);
    }
}
