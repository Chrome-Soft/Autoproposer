<?php

namespace App\Http\Controllers\Api;

use App\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        return (new Partner())->getListData(\request()->paging, \request()->filters);
    }

    public function restore(string $slug)
    {
        $partner = Partner::onlyTrashed()->where('slug', $slug)->first();
        if (!$partner->trashed())
            return response()->json(['message' => 'Aktív partner nem aktiválható'], 400);

        $partner->restore();

        return response()->json('ok');
    }

    public function userDataStatistics()
    {
        return Partner::getUserDataStatistics();
    }
}
