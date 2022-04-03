<?php

namespace App\Providers;

use App\Criteria;
use App\Currency;
use App\ProductAttributeType;
use App\ProposerItemType;
use App\ProposerType;
use App\Relation;
use App\SegmentAppearanceTemplate;
use App\SegmentProductPriority;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
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
        if (!App::environment('testing')) {
            if (Schema::hasTable('currencies')) {
                Cache::rememberForever('currencies', function () {
                    return Currency::all();
                });
            }

            if (Schema::hasTable('product_attribute_types')) {
                Cache::rememberForever('attributeTypes', function () {
                    return ProductAttributeType::all();
                });
            }

            if (Schema::hasTable('proposer_item_types')) {
                Cache::rememberForever('proposer_item_types', function () {
                    return ProposerItemType::all();
                });
            }

            if (Schema::hasTable('relations')) {
                Cache::rememberForever('relations', function () {
                    return Relation::all();
                });
            }

            if (Schema::hasTable('criterias')) {
                Cache::rememberForever('criterias', function () {
                    return Criteria::all();
                });
            }

            if (Schema::hasTable('segment_product_priorities')) {
                Cache::rememberForever('priorities', function () {
                    return SegmentProductPriority::all();
                });
            }

            if (Schema::hasTable('proposer_types')) {
                Cache::rememberForever('proposer_types', function () {
                    return ProposerType::all();
                });
            }

            if (Schema::hasTable('segment_appearance_templates')) {
                Cache::rememberForever('segment_appearance_templates', function () {
                    return SegmentAppearanceTemplate::all();
                });
            }
        }
    }
}
