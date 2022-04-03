<?php

namespace App\Http\Controllers\Api;

use App\Interaction;
use App\Product;
use App\ProductPhoto;
use App\Proposer;
use App\ProposerItem;
use App\Segment;
use App\Services\ProductImport\ProductImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * @var ProductImportService
     */
    private $importService;

    public function __construct(ProductImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        return (new Product)->getListData(\request()->paging, \request()->filters);
    }

    public function import()
    {
        try {
            $stat = $this->importService->import();
            return response()->json($stat);
        } catch (\Exception $ex) {
            Log::error($ex);
            return response()->json(['message' => 'Something went wrong'], 400);
        }
    }

    public function autocomplete(Request $request)
    {
        $segment = Segment::find($request->get('segment'));
        if (!$segment) return [];
        if (empty($request->get('q')))  return [];

        return (new Product())->autocomplete($segment,$request->get('q'));
    }
}
