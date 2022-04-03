<?php

namespace App\Http\Controllers\Api;

use App\ProductAttribute;

class ProductAttributeController extends Controller
{
    public function index()
    {
        return (new ProductAttribute)->getListData(\request()->paging, \request()->filters);
    }
}
