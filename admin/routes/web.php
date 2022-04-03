<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', 'Web\HomeController@index');
Route::get('/home', 'Web\HomeController@index')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/partners', 'Web\PartnerController@index');
    Route::get('/partners/create', 'Web\PartnerController@create');
    Route::post('/partners', 'Web\PartnerController@store');
    Route::get('/partners/{partner}', 'Web\PartnerController@show');
    Route::get('/partners/{partner}/edit', 'Web\PartnerController@edit');
    Route::patch('/partners/{partner}', 'Web\PartnerController@update');
    Route::delete('/partners/{partner}', 'Web\PartnerController@destroy');

    // Proposer
    Route::get('/proposers', 'Web\ProposerController@index');
    Route::get('/proposers/create', 'Web\ProposerController@create');
    Route::post('/proposers', 'Web\ProposerController@store');
    Route::get('/proposers/{proposer}', 'Web\ProposerController@show');
    Route::get('/proposers/{proposer}/edit', 'Web\ProposerController@edit');
    Route::patch('/proposers/{proposer}', 'Web\ProposerController@update');
    Route::delete('/proposers/{proposer}', 'Web\ProposerController@destroy');
    Route::get('/proposers/{proposer}/preview', 'Web\ProposerController@preview');

    // Proposer item
    Route::get('/proposers/{proposer}/items/create', 'Web\ProposerItemController@create');
    Route::post('/proposers/{proposer}/items', 'Web\ProposerItemController@store');
    Route::get('/proposers/{proposer}/items/{proposerItem}/edit', 'Web\ProposerItemController@edit')->name('proposer-item.edit');
    Route::patch('/proposers/{proposer}/items/{proposerItem}', 'Web\ProposerItemController@update');
    Route::delete('/proposers/{proposer}/items/{proposerItem}', 'Web\ProposerItemController@destroy')->name('proposer-item.delete');

    // Products
    Route::post('/products', 'Web\ProductController@store');
    Route::get('/products/create', 'Web\ProductController@create');
    Route::get('/products/import', 'Web\ProductController@import');
    Route::get('/products/{product}', 'Web\ProductController@show');
    Route::delete('/products/{product}', 'Web\ProductController@destroy');
    Route::get('/products', 'Web\ProductController@index');
    Route::get('/products/{product}/edit', 'Web\ProductController@edit');
    Route::patch('/products/{product}', 'Web\ProductController@update');

    Route::delete('/products/{product}/photos/{photo}', 'Web\ProductPhotoController@destroy');

    // Product attributes
    Route::get('/product-attributes', 'Web\ProductAttributeController@index');
    Route::get('/product-attributes/create', 'Web\ProductAttributeController@create');
    Route::get('/product-attributes/{productAttribute}/edit', 'Web\ProductAttributeController@edit');
    Route::post('/product-attributes', 'Web\ProductAttributeController@store');
    Route::patch('/product-attributes/{productAttribute}', 'Web\ProductAttributeController@update');
    Route::get('/product-attributes/{productAttribute}', 'Web\ProductAttributeController@show');
    Route::delete('/product-attributes/{productAttribute}', 'Web\ProductAttributeController@destroy');

    // Segments
    Route::get('/segments', 'Web\SegmentController@index');
    Route::get('/segments/create', 'Web\SegmentController@create');
    Route::get('/segments/{segment}', 'Web\SegmentController@show');
    Route::get('/segments/{segment}/edit', 'Web\SegmentController@edit');
    Route::delete('/segments/{segment}', 'Web\SegmentController@destroy');
});

// Ez azért nincs auth middleware -ben, mert iframe -ből húzzák be az urlt, tehát nincs se bejelentkezett user
// se header, vagy post paraméterek. Hitelesítés Controller -ben történik
Route::get('/proposers/{proposer}/recommendations/{cookie_id}', 'Web\ProposerController@iframe');
