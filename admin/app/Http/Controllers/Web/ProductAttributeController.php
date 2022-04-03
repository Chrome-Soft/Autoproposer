<?php

namespace App\Http\Controllers\Web;

use App\HasSlug;
use App\Model;
use App\ProductAttribute;

class ProductAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('product-attributes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('product-attributes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        return $this->storeBase(function (array $values) {
            return ProductAttribute::create(array_merge($values, [
                'user_id'       => auth()->id()
            ]));
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ProductAttribute  $productAttribute
     * @return \Illuminate\Http\Response
     */
    public function show(ProductAttribute $productAttribute)
    {
        return view('product-attributes.show', compact('productAttribute'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ProductAttribute  $productAttribute
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductAttribute $productAttribute)
    {
        return view('product-attributes.edit', compact('productAttribute'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProductAttribute  $productAttribute
     * @return \Illuminate\Http\Response
     */
    public function update(ProductAttribute $productAttribute)
    {
        return $this->updateBase($productAttribute, function (array $values) {
            return array_merge($values, [
                'user_id'       => auth()->id()
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProductAttribute  $productAttribute
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductAttribute $productAttribute)
    {
        $this->authorize('delete', $productAttribute);
        $productAttribute->delete();

        return redirect('/product-attributes')
            ->with('flash', 'Sikeres törlés');
    }

    protected function validateRequest(Model $productAttribute = null)
    {
        $name = 'required|';
        $name .= $productAttribute ? 'unique:product_attributes,name,' . $productAttribute->id : 'unique:product_attributes,name';

        return \request()->validate([
            'name'              => $name,
            'type_id'           => ['required', 'exists:product_attribute_types,id'],
        ]);
    }
}
