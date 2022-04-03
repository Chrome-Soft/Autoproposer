<?php

namespace App\Http\Controllers\Web;

use App\Model;
use App\Page;
use App\Partner;
use App\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class PageController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function edit(Partner $partner)
    {
        return view('partners.edit', compact('partner'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function update(Partner $partner)
    {
        return $this->updateBase($partner, function (array $values) {
            return array_merge($values, [
                'is_anonymus_domain'    => $this->getIsAnonym()
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);
        $page->delete();

        return redirect('/partners')
            ->with('flash', 'Sikeres inaktiválás');
    }

    protected function validateRequest(Model $partner = null)
    {
        $url = 'required|';
        $url .= $partner ? 'unique:partners,url,' . $partner->id : 'unique:partners,url';

        return \request()->validate([
            'name'  => ['required'],
            'url'   => $url
        ]);
    }

    protected function getIsAnonym()
    {
        return \request()->is_anonymus_domain == 'on' ? true : false;
    }
}
