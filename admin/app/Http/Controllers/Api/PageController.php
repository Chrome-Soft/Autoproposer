<?php

namespace App\Http\Controllers\Api;

use App\Interaction;
use App\Page;
use App\Partner;
use App\Product;
use App\ProductPhoto;
use App\Proposer;
use App\ProposerItem;
use App\Segment;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\Services\ProductImport\ProductImportService;
use App\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        return (new Page)->getListData(\request()->paging, \request()->filters);
    }

    public function store(Request $request)
    {
        return $this->save(new Page, $request);
    }

    public function update(Request $request, Page $page)
    {
        return $this->save($page, $request);
    }

    protected function save(Page $page, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'url'       => 'required',
        ]);

        if ($validator->fails())
            return response()
                ->json($validator->errors(), 422);

        $page->name = $request->name;
        $page->url = $this->sanitizeUrl($request->url);
        $page->slug = Str::slug($request->name . '-' . $request->partner_id);
        $page->partner_id = Str::slug($request->partner_id);
        $page->save();

        return $page;
    }

    protected function sanitizeUrl($url)
    {
        if (Str::startsWith($url, '/')) {
            return $url;
        }

        return "/{$url}";
    }

    public function destroy(Page $page)
    {
        $this->authorize('update', $page);
        $page->delete();

        return response()->json('ok');
    }
}
