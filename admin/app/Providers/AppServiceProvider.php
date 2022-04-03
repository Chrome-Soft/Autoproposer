<?php

namespace App\Providers;

use App\Currency;
use App\Partner;
use App\Product;
use App\ProductAttributeType;
use App\ProposerItemType;
use App\Services\ProductImport\ImportOptions;
use App\Services\ProductImport\ProductImportService;
use App\Services\Recommender\Fallback\Fallback;
use App\Services\Recommender\Fallback\PopularProductFallback;
use App\Services\Recommender\RecommenderService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Contracts\IHttpClient',
            'App\Services\HttpClient'
        );

        $this->app->bind(
            'App\Services\Recommender\IRecommenderService',
            'App\Services\Recommender\RecommenderService'
        );

        $this->app->when(RecommenderService::class)
            ->needs('$baseUrl')
            ->give(env('RECOMMENDER_URL'));

        $options = new ImportOptions(env('URL'), env('IMPORT_ENDPOINT'), env('API_KEY_HASH'), env('API_KEY_ENDPOINT'));
        $this->app->instance(ImportOptions::class, $options);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        \Carbon\Carbon::setLocale(config('app.locale'));

        if ($this->app->environment() == 'local') {
            DB::listen(function($query) {
                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL
                );
            });
        }
    }
}
