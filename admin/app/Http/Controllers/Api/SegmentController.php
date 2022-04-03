<?php

namespace App\Http\Controllers\Api;

use App\Console\Kernel;
use App\Interaction;
use App\Jobs\SegmentifyChunk;
use App\Product;
use App\ProductPhoto;
use App\Proposer;
use App\ProposerItem;
use App\Rules\SegmentMustHaveUniqueGroups;
use App\Segment;
use App\SegmentAppearanceTemplate;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\Services\ProductImport\ProductImportService;
use App\UserData;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SegmentController extends Controller
{
    /**
     * @var Kernel
     */
    private $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function index()
    {
        return (new Segment)->getListData(\request()->paging, \request()->filters);
    }

    protected function save(Request $request, Segment $segment)
    {
        $isNew = !$segment->id;
        $name = 'required|';
        $name .= $segment->id ? 'unique:segments,name,' . $segment->id : 'unique:segments,name';

        $validator = $segment->is_default
            ? Validator::make($request->all(), ['name' => $name])
            : Validator::make($request->all(), [
                'name'                          => $name,
                'groups'                        => 'required',
                'groups.*.criterias'            => 'required',
                'groups.*.criterias.*.criteria' => 'required|exists:criterias,id',
                'groups.*.criterias.*.relation' => 'required|exists:relations,id',
                'groups.*.criterias.*.value'    => 'required_if:groups.*.criterias.*.relation,1|required_if:groups.*.criterias.*.relation,2|required_if:groups.*.criterias.*.relation,3|required_if:groups.*.criterias.*.relation,4|required_if:groups.*.criterias.*.relation,5|required_if:groups.*.criterias.*.relation,6'
            ]);

        if ($validator->fails())
            return response()
                ->json($validator->errors(), 422);

        $segment->name = $request->name;
        $segment->description = $request->description;
        $segment->user_id = auth('api')->id();
        $segment->template_id = $request->template_id;

        $segment->save();
        $segment->syncGroups($request->groups);
        $segment = $segment->refresh();

        // TODO ezt jó lenne validációval megoldani
        if ($segment->hasSame()) {
            // Ha create oldaoon van, akkor töröljük a létrehozott szegmenst
            if ($isNew) {
                // Azért forceDelete() mert SoftDelete modelről van szó
                $segment->forceDelete();
            }
            return response()
                ->json(['groups' => 'Létezik már szegmens pontosan ilyen csoportokkal és/vagy feltételekkel'], 422);
        }

        $this->runSegmentify($segment);

        return response()->json($segment);
    }

    public function store(Request $request)
    {
        return $this->save($request, new Segment);
    }

    public function update(Request $request, Segment $segment)
    {
        return $this->save($request, $segment);
    }

    public function restore(string $slug)
    {
        /** @var Segment $segment */
        $segment = Segment::onlyTrashed()->where('slug', $slug)->first();
        if (!$segment->trashed())
            return response()->json(['message' => 'Aktív szegmens nem aktiválható'], 400);

        $segment->restore();
        $this->runSegmentify($segment);

        return response()->json('ok');
    }

    public function userData(Segment $segment)
    {
        return (new UserData)->getListData($segment, request()->paging, \request()->filters);
    }

    public function userDataStatistics()
    {
        return Segment::getUserDataStatistics();
    }

    public function segmentify(Segment $segment)
    {
        $this->runSegmentify($segment);

        return response()->json(null, 204);
    }

    public function replicate(Segment $segment)
    {
        $replicate = $segment->replicate();

        return response()->json($replicate);
    }

    public function sequence()
    {
        $max = Segment::select('sequence')->orderByDesc('sequence')->take(1)->first()->sequence;
        return $max + 1;
    }

    public function getTemplate($templateId)
    {
        return SegmentAppearanceTemplate::where('id', $templateId)->firstOrFail();
    }

    protected function runSegmentify(Segment $segment)
    {
        if ($segment->is_default) return;
        
        $this->kernel->call('segment:segmentify', [
            'segment'   => $segment->slug
        ]);
    }
}
