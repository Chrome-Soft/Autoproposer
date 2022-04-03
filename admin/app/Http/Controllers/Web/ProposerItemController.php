<?php

namespace App\Http\Controllers\Web;

use App\Model;
use App\Product;
use App\Proposer;
use App\ProposerItem;
use App\ProposerItemType;
use App\Rules\MaxItemCount;
use App\Services\PhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProposerItemController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Proposer $proposer)
    {
        $products = Product::all();
        return view('proposer-items.create', compact('proposer', 'products'));
    }

    public function store(Proposer $proposer)
    {
        $this->validateRequest();

        $proposerItem = new ProposerItem;
        $proposerItem->proposer_id = $proposer->id;
        $proposerItem->user_id = auth()->id();
        $proposerItem->type_id = ProposerItemType::getByKey(\request()->type)->id;
        $proposerItem->link = \request()->link;

        $this->setContent($proposerItem);

        $proposerItem->save();

        $this->uploadPhoto($proposerItem);

        return redirect($proposer->path())
            ->with('flash', 'Sikeres létrehozás');
    }

    protected function setContent(ProposerItem $proposerItem)
    {
        switch (\request()->type) {
            case ProposerItemType::TYPE_PRODUCT:
                $proposerItem->product_id = \request()->product_id;
                break;
            case ProposerItemType::TYPE_HTML:
                $proposerItem->html_content = \request()->html_content;
                break;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ProposerItem  $proposerItem
     * @return \Illuminate\Http\Response
     */
    public function show(ProposerItem $proposerItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ProposerItem  $proposerItem
     * @return \Illuminate\Http\Response
     */
    public function edit(Proposer $proposer, ProposerItem $proposerItem)
    {
        $products = Product::all();
        return view('proposer-items/edit', compact('proposer', 'proposerItem', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProposerItem  $proposerItem
     * @return \Illuminate\Http\Response
     */
    public function update(Proposer $proposer, ProposerItem $proposerItem)
    {
        $this->validateRequest($proposerItem);

        $proposerItem->proposer_id = $proposer->id;
        $proposerItem->user_id = auth()->id();
        $proposerItem->type_id = ProposerItemType::getByKey(\request()->type)->id;
        $proposerItem->link = \request()->link;

        $this->setContent($proposerItem);

        $proposerItem->save();

        $this->uploadPhoto($proposerItem);

        return redirect($proposer->path())
            ->with('flash', 'Sikeres szerkesztés');
    }

    protected function uploadPhoto(ProposerItem $proposerItem)
    {
        $photo = request()->file('image');

        if (!$this->shouldUploadPhoto($photo)) {
            return;
        }

        $this->photoService->uploadPhotos($proposerItem, [$photo], auth()->id());
    }

    protected function shouldUploadPhoto($photo)
    {
        return ($photo && \request()->type == ProposerItemType::TYPE_IMAGE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProposerItem  $proposerItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(Proposer $proposer, ProposerItem $proposerItem)
    {
        $this->authorize('delete', $proposer);
        $proposerItem->delete();

        return redirect($proposer->path());
    }

    protected function validateRequest(Model $model = null)
    {
        $proposerId = ['required', 'exists:proposers,id'];

        if (\request()->isMethod('POST')) {
            $proposerId[] = new MaxItemCount;
        }

        $rules = [
            'proposer_id'       => $proposerId,
            'type'              => ['required', 'exists:proposer_item_types,key'],
            'html_content'      => ['required_if:type,html'],
            'product_id'        => ['required_if:type,product']
        ];

        if (\request('type') == ProposerItemType::TYPE_PRODUCT) {
            $rules['product_id'][] = 'exists:products,id';
        }

        if (\request('type') == ProposerItemType::TYPE_IMAGE) {
            $rules['link']= ['url'];
        }

        if (!$model) {
            $rules['image'] = ['required_if:type,image', 'image', 'mimes:jpg,jpeg,png', 'between:1,10240'];
        }

        $values = \request()->validate($rules);

        if (\request('type') != ProposerItemType::TYPE_PRODUCT) {
            $values['product_id'] = null;
        }

        return Arr::except($values, ['image']);
    }
}
