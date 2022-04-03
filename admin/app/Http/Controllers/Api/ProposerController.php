<?php

namespace App\Http\Controllers\Api;

use App\Partner;
use App\Proposer;

class ProposerController extends Controller
{
    public function index()
    {
        return (new Proposer)->getListData(\request()->paging, \request()->filters);
    }

    public function byPageUrl(string $partnerExternalId)
    {
        $cleanedPageUrl = Proposer::getCleanedPageUrl(request()->pageUrl);

        $partner = Partner::where('external_id', $partnerExternalId)->firstOrFail();
        $proposer = Proposer::where('page_url', $cleanedPageUrl)->where('partner_id', $partner->id)->firstOrFail();

        return $proposer;
    }
}
