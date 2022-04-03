<?php

namespace App\Providers;

use App\Currency;
use App\ProductAttributeType;
use App\ProposerItemType;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer(
            ['proposer-items.create', 'proposer-items.edit'],
            function ($view) {
                $view->with('proposerItemTypes', Cache::get('proposer_item_types'));
            });

        View::composer(
            ['segments.create', 'segments.edit'],
            function ($view) {
                $view->with('criterias', Cache::get('criterias'));
            });

        View::composer(
            ['products.create', 'products.edit', 'product-attributes.create', 'product-attributes.edit'], 'App\Http\View\Composers\ProductComposer');

        View::composer(
            ['segments.show'],
            function ($view) {
                $view->with('priorities', Cache::get('priorities'));
            });
    }
}
