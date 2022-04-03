<?php

namespace App\Http\Controllers\Web;

use App\Model;
use App\Product;
use App\ProductPhoto;
use Illuminate\Auth\Access\AuthorizationException;

class ProductPhotoController extends Controller
{
    public function destroy(Product $product, ProductPhoto $photo)
    {
        try {
            $this->authorize('delete', $product);
            $photo->delete();
        } catch (AuthorizationException $aex) {
            return response()->json('Access denied', 403);
        } catch (\Exception $e) {
            return response()->json('Something went wrong', 500);
        }

        return response()->json('ok', 200);
    }

    protected function validateRequest(Model $model = null)
    {
        // TODO: Implement validateRequest() method.
    }
}
