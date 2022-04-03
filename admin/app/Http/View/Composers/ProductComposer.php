<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Http\View\Composers;

use App\Currency;
use App\ProductAttribute;
use App\ProductAttributeType;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProductComposer
{
    public function compose(View $view)
    {
        $view->with('currencies', Cache::get('currencies'));
        $view->with('attributeTypes', Cache::get('attributeTypes'));

        $view->with('attributes', ProductAttribute::select(['id', 'name', 'type_id'])->where('is_imported', false)->get());
    }
}
