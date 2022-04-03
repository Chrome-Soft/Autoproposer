<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API for external consumer apps
Route::middleware(['auth.apikey', 'throttle:120,1'])->group(function () {
    Route::post('/user-data', 'Api\UserDataController@store');
    Route::patch('/user-data/register', 'Api\UserDataController@register');
    Route::post('/user-data/search-term', 'Api\UserDataController@storeSearchTerm');
    Route::get('/user-data/csv', 'Api\UserDataController@getCsv');

    Route::post('/page-load', 'Api\PageLoadController@store');

    Route::post('/interaction', 'Api\InteractionController@store');

    Route::post('/proposer/{partnerExternalId}', 'Api\ProposerController@byPageUrl');
});

// TODO TODO TODO
Route::get('/recommendation/{cookieId}/{proposer}', 'Api\RecommendationController@index');
Route::post('/interaction-iframe', 'Api\InteractionController@store');
Route::get('/segments/sequence', 'Api\SegmentController@sequence');

// API for internal users
Route::middleware(['auth:api'])->group(function () {
    Route::get('/proposers/{proposer}/items', 'Api\ProposerItemController@index');
    Route::delete('/proposers/{proposer}/items/{proposerItem}', 'Api\ProposerItemController@destroy');

    Route::get('/products/{product}/photos', 'Api\ProductPhotoController@index');
    Route::get('/products/autocomplete', 'Api\ProductController@autocomplete');

    Route::delete('/products/{product}/photos/{productPhoto}', 'Api\ProductPhotoController@destroy');

    Route::post('/products/import', 'Api\ProductController@import');

    Route::post('/segments', 'Api\SegmentController@store');
    Route::patch('/segments/{segment}', 'Api\SegmentController@update');

    Route::get('/criterias', function () {
        return \App\Criteria::all();
    });
    Route::get('/relations', function () {
        return \App\Relation::all();
    });
    Route::get('/products', function () {
        return \App\Product::all();
    });
    Route::delete('/segment-products/{segmentProduct}', 'Api\SegmentProductController@destroy');
    Route::post('/segment-products', 'Api\SegmentProductController@store');

    // Pages
    Route::delete('/pages/{page}', 'Api\PageController@destroy');
    Route::post('/pages', 'Api\PageController@store');
    Route::patch('/pages/{page}', 'Api\PageController@update');

    // Listák
    Route::post('/products/list', 'Api\ProductController@index');
    Route::post('/proposers/list', 'Api\ProposerController@index');
    Route::post('/segments/list', 'Api\SegmentController@index');
    Route::post('/partners/list', 'Api\PartnerController@index');
    Route::post('/product-attributes/list', 'Api\ProductAttributeController@index');
    Route::post('/segments/{segment}/user-data', 'Api\SegmentController@userData');
    Route::post('/pages/list', 'Api\PageController@index');

    Route::patch('/partners/{slug}/restore', 'Api\PartnerController@restore');
    Route::patch('/segments/{slug}/restore', 'Api\SegmentController@restore');

    Route::get('/page-load', 'Api\PageLoadController@index');

    Route::get('/criterias/{id}/options', 'Api\CriteriaController@options');
    Route::get('/criterias/{id}/availableRelations', 'Api\CriteriaController@availableRelations');

    Route::get('/partners/user-data-statistics', 'Api\PartnerController@userDataStatistics');
    Route::get('/segments/user-data-statistics', 'Api\SegmentController@userDataStatistics');
    Route::post('/segments/{segment}/segmentify', 'Api\SegmentController@segmentify');
    Route::post('/segments/{segment}/replicate', 'Api\SegmentController@replicate');

    Route::get('/segment-appearance-templates/{templateId}', 'Api\SegmentController@getTemplate');
});
