<?php

namespace App\Http\Controllers\Web;

use App\Model;
use App\Partner;
use App\Product;
use App\ProductAttribute;
use App\Rules\AtLeastOnePriceRequired;
use App\Services\PhotoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class ProductController extends Controller
{
    /**
     * @var PhotoService
     */
    private $photoService;

    public function __construct(PhotoService $photoService)
    {
        $this->photoService = $photoService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $prices = [];
        $canUpdate = true;
        return view('products.create', compact('prices', 'canUpdate'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        return $this->storeBase(function (array $values) {
            $product = Product::create([
                'name'          => \request()->name,
                'description'   => \request()->description,
                'link'          => \request()->link
            ]);

            $product->syncPrices($values['prices'], \request()->currencies, auth()->id());
            $this->photoService->uploadPhotos($product, Arr::get($values, 'photos', []), auth()->id());
            $product->syncAttributes(\request()->attribute_ids ?? [], \request()->attribute_values ?? []);

            return $product;
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Partner  $partner
     * @return Response
     */
    public function show(Product $product)
    {
        $product->setAppends(['all_present', 'all_view']);
        $view = $product->all_view;
        $present = $product->all_present;

        return view('products.show', compact('product', 'present', 'view'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Product $product
     * @return Response
     */
    public function edit(Product $product)
    {
        $prices = [];
        $canUpdate = Auth::user()->can('update', $product);

        foreach ($product->prices as $price) {
            $prices[$price->currency_id] = $price->price;
        }

        return view('products.edit', compact('product', 'prices', 'canUpdate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Product $product
     * @return Response
     */
    public function update(Product $product)
    {
        $this->authorize('update', $product);
        $values = $this->validateRequest($product);

        try {
            $product->name = \request()->name;
            $product->description = \request()->description;
            $product->link = \request()->link;
            $product->update();

            $product->syncPrices($values['prices'], \request()->currencies, auth()->id());
            $this->photoService->uploadPhotos($product, Arr::get($values, 'photos', []), auth()->id());
            $product->syncAttributes(\request()->attribute_ids ?? [], \request()->attribute_values ?? []);

            return redirect($product->path())
                ->with('flash', 'Sikeres szerkesztés');
        } catch (\Exception $e) {
            Log::error($e);

            return back()
                ->withErrors(['error' => 'Ismeretlen hiba történt'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();

        return redirect('/products')
            ->with('flash', 'Sikeres törlés');
    }

    public function import()
    {
        $url = Config::get('url');
        $endpoint = Config::get('endpoint');

        if (!$url || !$endpoint) {
            abort(501, 'text');
        }

        return view('products.import', compact('url', 'endpoint'));
    }

    protected function validateRequest(Model $product = null)
    {
        $name = 'required|';
        $name .= $product ? 'unique:products,name,' . $product->id : 'unique:products,name';

        $rules = [
            'name'          => $name,
            'prices'        => ['required', new AtLeastOnePriceRequired],
            'attribute_ids' => ['sometimes', 'required', 'exists:product_attributes,id'],
            'link'          => ['url']
        ];

        foreach (\request()->photos ?? [] as $i => $value) {
            $rules["photos.{$i}"] = 'image|mimes:jpeg,jpg,png|max:10240';
        }

        foreach (\request()->attribute_values ?? [] as $i => $value) {
            $rules["attribute_values.{$i}"] = 'required';
        }

        return \request()->validate($rules);
    }
}
