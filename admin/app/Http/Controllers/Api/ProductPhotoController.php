<?php

namespace App\Http\Controllers\Api;

use App\Interaction;
use App\Product;
use App\ProductPhoto;
use App\Proposer;
use App\ProposerItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use mysql_xdevapi\Exception;

class ProductPhotoController extends Controller
{
    public function index(Product $product)
    {
        return response()->json([
            'photos' => $product->photos
        ]);
    }

    public function destroy(Product $product, ProductPhoto $productPhoto)
    {
        $this->authorize('update', $product);
        $productPhoto->delete();

        return response()->json('OK');
    }
}
