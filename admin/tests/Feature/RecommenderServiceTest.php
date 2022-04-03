<?php

namespace Tests\Feature;

use App\Criteria;
use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttributeType;
use App\ProductPhoto;
use App\ProductProductAttribute;
use App\ProposerItem;
use App\ProposerItemType;
use App\Recommendation;
use App\Relation;
use App\Segment;
use App\SegmentAppearanceTemplate;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\SegmentProduct;
use App\SegmentProductPriority;
use App\Services\HttpClient;
use App\Services\Recommender\Fallback\FallbackComposition;
use App\Services\Recommender\Fallback\FallbackFactory;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecommenderServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
//        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'SegmentProductPriorityTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProposerItemTypeTableSeeder']);
    }

    /** @test */
    public function it_returns_empty_list_if_empty_list_was_returned_from_recommender_api()
    {
        Cache::shouldReceive('get')
            ->times(1)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());
        
        $this->signIn();

        $segment = create('Segment');
        $proposer = create('Proposer', 1, ['max_item_number' => 2]);

        $clientMock = $this->createMock(HttpClient::class);
        $clientMock->method('post')
            ->willReturn([]);

        $recommenderService = $this->createService($clientMock);
        $products = $recommenderService->recommend($segment, $proposer);

        $this->assertCount(0, $products);
    }

    /** @test */
    public function it_returns_the_correct_segment_for_a_sequence()
    {
        // Egyéb szegmens nem kell
        Segment::truncate();

        $clientMock = $this->createMock(HttpClient::class);
        $clientMock->method('post')
            ->willReturn([]);

        $recommenderService = $this->createService($clientMock);

        $segment = new Segment;
        $segment->id = 6;
        $segment->slug = 'holland-fiatalok';
        $segment->name = 'Holland fiatalok';
        $segment->sequence = 0;
        $segment->save();

        $segment = new Segment;
        $segment->id = 10;
        $segment->slug = 'osztrak-kozepkoru-kozmetikai-turizmus';
        $segment->name = 'Osztrák középkorú kozmetikai turizmus*';
        $segment->sequence = 1;
        $segment->save();

        $segment = new Segment;
        $segment->id = 9;
        $segment->slug = 'orosz-gyogyfurdoturizmus';
        $segment->name = 'Orosz gyógyfürdőturizmus*';
        $segment->sequence = 2;
        $segment->save();

        $segment = new Segment;
        $segment->id = 4;
        $segment->slug = 'egyszeru-angol-turista';
        $segment->name = 'Egyszerű angol turista*';
        $segment->sequence = 3;
        $segment->save();

        $segment = new Segment;
        $segment->id = 5;
        $segment->slug = 'nyugat-europai-ertelmisegi';
        $segment->name = 'Nyugat európai értelmiségi*';
        $segment->sequence = 4;
        $segment->save();

        $segment = new Segment;
        $segment->id = 7;
        $segment->slug = 'hipszter-kultura';
        $segment->name = 'Hipszter-kultúra*';
        $segment->sequence = 5;
        $segment->save();

        $segment = new Segment;
        $segment->id = 11;
        $segment->slug = 'egyeb';
        $segment->name = 'Egyéb';
        $segment->sequence = 6;
        $segment->is_default = true;
        $segment->save();

        $segment = $this->invokeMethod($recommenderService, 'mapSegmentifyResult', [[[0.89, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1]]]);
        $this->assertEquals(6, $segment->id);

        $segment = $this->invokeMethod($recommenderService, 'mapSegmentifyResult', [[[0.1, 0.9, 0.1, 0.1, 0.1, 0.1, 0.1]]]);
        $this->assertEquals(10, $segment->id);

        $segment = $this->invokeMethod($recommenderService, 'mapSegmentifyResult', [[[0.1, 0.1, 0.9, 0.1, 0.1, 0.1, 0.1]]]);
        $this->assertEquals(9, $segment->id);

        $segment = $this->invokeMethod($recommenderService, 'mapSegmentifyResult', [[[0.1, 0.1, 0.1, 0.9, 0.1, 0.1, 0.1]]]);
        $this->assertEquals(4, $segment->id);

        $segment = $this->invokeMethod($recommenderService, 'mapSegmentifyResult', [[[0.1, 0.1, 0.1, 0.1, 0.9, 0.1, 0.1]]]);
        $this->assertEquals(5, $segment->id);

        $segment = $this->invokeMethod($recommenderService, 'mapSegmentifyResult', [[[0.1, 0.1, 0.1, 0.1, 0.1, 0.9, 0.1]]]);
        $this->assertEquals(7, $segment->id);

        $other = Segment::where('is_default', 1)->first();
        $segment = $this->invokeMethod($recommenderService, 'mapSegmentifyResult', [[[0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.9]]]);
        $this->assertEquals($other->id, $segment->id);
    }

    /** @test */
    public function it_stores_the_returned_product_as_recommendations()
    {
        $product1 = create('Product');
        $product2 = create('Product');

        $segment = create('Segment');

        $clientMock = $this->createMock(HttpClient::class);
        $clientMock->method('post')
            ->willReturn([
                $segment->id => [$product1->id, $product2->id]
            ]);

        $recommenderService = $this->createService($clientMock);

        $recommenderService->storeRecommendations([5]);

        $recommendations = Recommendation::all();
        $this->assertCount(2, $recommendations);

        $this->assertDatabaseHas('recommendations', [
            'segment_id'    => $segment->id,
            'product_id'    => $product1->id,
            'order'         => 1
        ]);
        $this->assertDatabaseHas('recommendations', [
            'segment_id'    => $segment->id,
            'product_id'    => $product2->id,
            'order'         => 2
        ]);
    }

    /** @test */
    public function it_stores_the_returned_products_as_recommendations()
    {
        $product1 = create('Product');
        $product2 = create('Product');

        $segment1 = create('Segment');
        $segment2 = create('Segment');

        $clientMock = $this->createMock(HttpClient::class);
        $clientMock->method('post')
            ->willReturn([
                $segment1->id => [$product1->id],
                $segment2->id => [$product2->id]
            ]);

        $recommenderService = $this->createService($clientMock);

        $recommenderService->storeRecommendations([5]);

        $recommendations = Recommendation::all();
        $this->assertCount(2, $recommendations);

        $this->assertDatabaseHas('recommendations', [
            'segment_id'    => $segment1->id,
            'product_id'    => $product1->id,
            'order'         => 1
        ]);
        $this->assertDatabaseHas('recommendations', [
            'segment_id'    => $segment2->id,
            'product_id'    => $product2->id,
            'order'         => 1
        ]);
    }

    /** @test */
    public function it_gets_empty_data()
    {
        $clientMock = $this->createMock(HttpClient::class);
        $clientMock->method('post')
            ->willReturn([]);

        $recommenderService = $this->createService($clientMock);

        $recommenderService->storeRecommendations([5]);

        $recommendations = Recommendation::all();
        $this->assertCount(0, $recommendations);
    }

    /** @test */
    public function it_empty_old_recommendations()
    {
        $product1 = create('Product');
        $product2 = create('Product');

        $segment1 = create('Segment');
        $segment2 = create('Segment');

        $recommendation = new Recommendation;
        $recommendation->segment_id = $segment1->id;
        $recommendation->product_id = $product1->id;
        $recommendation->save();

        $clientMock = $this->createMock(HttpClient::class);
        $clientMock->method('post')
            ->willReturn([
                $segment2->id => [$product2->id]
            ]);

        $recommenderService = $this->createService($clientMock);

        $recommenderService->storeRecommendations([5]);

        $recommendations = Recommendation::all();
        $this->assertCount(1, $recommendations);

        $this->assertDatabaseHas('recommendations', [
            'segment_id'    => $segment2->id,
            'product_id'    => $product2->id,
            'order'         => 1
        ]);
        $this->assertDatabaseMissing('recommendations', [
            'segment_id'    => $segment1->id,
            'product_id'    => $product1->id
        ]);
    }

    /** @test */
    public function it_does_not_empty_old_recommendations_if_empty_data_returned()
    {
        $product1 = create('Product');
        $product2 = create('Product');

        $segment1 = create('Segment');
        $segment2 = create('Segment');

        $recommendation = new Recommendation;
        $recommendation->segment_id = $segment1->id;
        $recommendation->product_id = $product1->id;
        $recommendation->save();

        $clientMock = $this->createMock(HttpClient::class);
        $clientMock->method('post')
            ->willReturn([]);

        $recommenderService = $this->createService($clientMock);

        $recommenderService->storeRecommendations([5]);

        $recommendations = Recommendation::all();
        $this->assertCount(1, $recommendations);

        $this->assertDatabaseHas('recommendations', [
            'segment_id'    => $segment1->id,
            'product_id'    => $product1->id
        ]);
    }
    
    /** @test */
    public function it_returns_recommendations_if_no_proposer_items_and_no_segment_products()
    {
        // 5 ajánlás, proposer item üres, szegmens termékek üresek

        $segment = create('Segment');
        $recommendations = create('Recommendation', 5, [
            'segment_id' => $segment->id
        ]);
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);

        Cache::shouldReceive('get')
            ->times(1)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $recommender = $this->createService($clientMock);
        $products = $recommender->recommend($segment, $proposer);

        $recommendationsIds = $recommendations->pluck('product_id')->all();
        $productIds = array_map(function ($x) { return $x['id']; }, $products);

        $this->assertEquals($recommendationsIds, $productIds);
    }

    /** @test */
    public function it_returns_only_3_recommendations_when_2_proposer_items_given()
    {
        // 2 Proposer item, 5 ajánlás. Megjelennek a proposer itemek

        $segment = create('Segment');
        $recommendations = create('Recommendation', 5, [
            'segment_id' => $segment->id
        ]);
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);

        $proposerItems = create('ProposerItem', 2, [
            'proposer_id'   => $proposer->id,
            'type_id'       => 1,    // html
            'product_id'    => null
        ]);

        Cache::shouldReceive('get')
            ->times(3)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $recommender = $this->createService($clientMock);
        $products = $recommender->recommend($segment, $proposer);

        $expectedIds = [$proposerItems[0]->id, $proposerItems[1]->id, $recommendations[0]->product_id, $recommendations[1]->product_id, $recommendations[2]->product_id];
        $productIds = array_map(function ($x) { return $x['id']; }, $products);

        $this->assertEquals($expectedIds, $productIds);
    }

    /** @test */
    public function it_returns_only_3_recommendations_when_2_segment_products_given()
    {
        // Proposer item üres, 2 always szegmens termék 5 ajánlás. Szegmens termékek megjelennek elől

        $segment = create('Segment');

        $product1 = create('Product');
        $product2 = create('Product');
        $segmentProduct1 = create('SegmentProduct', 1, ['priority_id' => 1, 'product_id' => $product1->id, 'segment_id' => $segment->id]);
        $segmentProduct2 = create('SegmentProduct', 1, ['priority_id' => 1, 'product_id' => $product2->id, 'segment_id' => $segment->id]);

        $recommendations = create('Recommendation', 5, [
            'segment_id' => $segment->id
        ]);
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);

        Cache::shouldReceive('get')
            ->times(1)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $recommender = $this->createService($clientMock);
        $products = $recommender->recommend($segment, $proposer);

        $expectedIds = [$product2->id, $product1->id, $recommendations[0]->product_id, $recommendations[1]->product_id, $recommendations[2]->product_id];
        $productIds = array_map(function ($x) { return $x['id']; }, $products);

        $this->assertEquals($expectedIds, $productIds);
    }

    /** @test */
    public function it_returns_optional_segment_products_if_there_are_available_slots()
    {
        // Proposer item üres, 1 always 1 optional szegmens termék. 3 ajánlás Always szegmens termék megjelenik elől, optional hátul

        $segment = create('Segment');

        $product1 = create('Product');
        $product2 = create('Product');
        $segmentProduct1 = create('SegmentProduct', 1, ['priority_id' => 1, 'product_id' => $product1->id, 'segment_id' => $segment->id]);
        $segmentProduct2 = create('SegmentProduct', 1, ['priority_id' => 2, 'product_id' => $product2->id, 'segment_id' => $segment->id]);

        $recommendations = create('Recommendation', 3, [
            'segment_id' => $segment->id
        ]);
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);

        Cache::shouldReceive('get')
            ->times(1)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $recommender = $this->createService($clientMock);
        $products = $recommender->recommend($segment, $proposer);

        $expectedIds = [$product1->id, $recommendations[0]->product_id, $recommendations[1]->product_id, $recommendations[2]->product_id, $product2->id];
        $productIds = array_map(function ($x) { return $x['id']; }, $products);

        $this->assertEquals($expectedIds, $productIds);
    }

    /** @test */
    public function it_does_not_returns_optional_segment_products_if_there_is_no_available_slots()
    {
        // Proposer item üres, 1 always 1 optional szegmens termék. 3 ajánlás Always szegmens termék megjelenik elől, optional hátul

        $segment = create('Segment');

        $product1 = create('Product');
        $product2 = create('Product');
        $segmentProduct1 = create('SegmentProduct', 1, ['priority_id' => 1, 'product_id' => $product1->id, 'segment_id' => $segment->id]);
        $segmentProduct2 = create('SegmentProduct', 1, ['priority_id' => 2, 'product_id' => $product2->id, 'segment_id' => $segment->id]);

        $recommendations = create('Recommendation', 4, [
            'segment_id' => $segment->id
        ]);
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);

        Cache::shouldReceive('get')
            ->times(1)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $recommender = $this->createService($clientMock);
        $products = $recommender->recommend($segment, $proposer);

        $expectedIds = [$product1->id, $recommendations[0]->product_id, $recommendations[1]->product_id, $recommendations[2]->product_id, $recommendations[3]->product_id];
        $productIds = array_map(function ($x) { return $x['id']; }, $products);

        $this->assertEquals($expectedIds, $productIds);
    }

    /** @test */
    public function it_return_proposer_items_always_at_the_beginning()
    {
        // Max 5 termék. 2 proposer item, 1 always, 1 optional szegmens termék. 5 ajánlás
        // Eredmény: proposer item, proposer item, always, ajánlás, ajánlás

        $segment = create('Segment');

        $product1 = create('Product');
        $product2 = create('Product');
        $segmentProduct1 = create('SegmentProduct', 1, ['priority_id' => 1, 'product_id' => $product1->id, 'segment_id' => $segment->id]);
        $segmentProduct2 = create('SegmentProduct', 1, ['priority_id' => 2, 'product_id' => $product2->id, 'segment_id' => $segment->id]);

        $recommendations = create('Recommendation', 5, [
            'segment_id' => $segment->id
        ]);
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);
        $proposerItems = create('ProposerItem', 2, [
            'proposer_id'   => $proposer->id,
            'type_id'       => 1    // html
        ]);

        Cache::shouldReceive('get')
            ->times(3)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $recommender = $this->createService($clientMock);
        $products = $recommender->recommend($segment, $proposer);

        $expectedIds = [$proposerItems[0]->id, $proposerItems[1]->id, $product1->id, $recommendations[0]->product_id, $recommendations[1]->product_id];
        $productIds = array_map(function ($x) { return $x['id']; }, $products);

        $this->assertEquals($expectedIds, $productIds);
    }

    // Proposer item, szegmens always termék, ajánlás, szegmens opcionális

    /** @test */
    public function it_returns_items_in_appropriate_order()
    {
        // Max 5 termék. 2 proposer item, 1 always, 1 optional szegmens termék. 1 ajánlás
        // Eredmény: proposer item, proposer item, always, ajánlás, optional

        $segment = create('Segment');

        $product1 = create('Product');
        $product2 = create('Product');
        $segmentProduct1 = create('SegmentProduct', 1, ['priority_id' => 1, 'product_id' => $product1->id, 'segment_id' => $segment->id]);
        $segmentProduct2 = create('SegmentProduct', 1, ['priority_id' => 2, 'product_id' => $product2->id, 'segment_id' => $segment->id]);

        $recommendation = create('Recommendation', 1, [
            'segment_id' => $segment->id
        ]);
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);
        $proposerItems = create('ProposerItem', 2, [
            'proposer_id'   => $proposer->id,
            'type_id'       => 1    // html
        ]);

        Cache::shouldReceive('get')
            ->times(3)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $recommender = $this->createService($clientMock);
        $products = $recommender->recommend($segment, $proposer);

        $expectedIds = [$proposerItems[0]->id, $proposerItems[1]->id, $product1->id, $recommendation->product_id, $product2->id];
        $productIds = array_map(function ($x) { return $x['id']; }, $products);

        $this->assertEquals($expectedIds, $productIds);
    }

    /** @test */
    public function it_appends_fallback_products_if_necessary()
    {
        // Max 5 termék. 2 proposer item, nincs szegmens termék se ajánlás
        // Eredmény: proposer item, proposer item, 3 fallback termék

        $segment = create('Segment');
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);
        $proposerItems = create('ProposerItem', 2, [
            'proposer_id'   => $proposer->id,
            'type_id'       => 1,    // html
            'product_id'    => null
        ]);

        Cache::shouldReceive('get')
            ->times(3)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $product1 = create('Product');
        $product2 = create('Product');

        $clientMock = $this->createMock(HttpClient::class);

        $fallbackCompositionMock = $this->createMock(FallbackComposition::class);
        $fallbackCompositionMock
            ->method('getProducts')
            // proposer max item: 5, proposer item: 2, tehát 3 fallback product
            ->with($this->isInstanceOf(Segment::class), $this->equalTo(3), $this->isType('array'))
            ->willReturn(collect([
                $product1, $product2
            ]));

        $fallbackFactoryMock = $this->createMock(FallbackFactory::class);
        $fallbackFactoryMock
            ->method('createDefaultComposition')
            ->willReturn($fallbackCompositionMock);

        $recommender = new RecommenderService($clientMock, $fallbackFactoryMock, 'http://base.url');
        $products = $recommender->recommend($segment, $proposer);

        $expectedIds = [$proposerItems[0]->id, $proposerItems[1]->id, $product1->id, $product2->id];
        $productIds = array_map(function ($x) { return $x['id']; }, $products);

        $this->assertEquals($expectedIds, $productIds);
    }

    /** @test */
    public function it_only_appends_unique_fallback_products()
    {
        $this->signIn();
        // Max 5 termék. 2 proposer item, nincs szegmens termék se ajánlás
        // Fallback termék csak 1 van, a többi ugyanaz, mint a proposer itemek, szóval azokat kizárjuk
        // Eredmény: proposer item, proposer item, 1 fallback termék

        $segment = create('Segment');
        $product1 = create('Product', 1, ['id' => 101]);
        $product2 = create('Product', 1, ['id' => 102]);
        $product3 = create('Product');

        $photo = new ProductPhoto;
        $photo->product_id = $product1->id;
        $photo->image_path = 'path';
        $photo->user_id = $this->signedInUser->id;
        $photo->save();

        $photo = new ProductPhoto;
        $photo->product_id = $product2->id;
        $photo->image_path = 'path';
        $photo->user_id = $this->signedInUser->id;
        $photo->save();

        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);
        $proposerItem1 = create('ProposerItem', 1, [
            'proposer_id'   => $proposer->id,
            'type_id'       => 3,    // html
            'product_id'    => $product1->id
        ]);
        $proposerItem2 = create('ProposerItem', 1, [
            'proposer_id'   => $proposer->id,
            'type_id'       => 3,    // html
            'product_id'    => $product2->id
        ]);

        Cache::shouldReceive('get')
            ->times(3)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $fallbackCompositionMock = $this->createMock(FallbackComposition::class);
        $fallbackCompositionMock
            ->method('getProducts')
            // product 1 és 2 excluded -nak számít a proposer itemek miatt
            ->with($this->isInstanceOf(Segment::class), $this->equalTo(3), $this->equalTo([$product1->id, $product2->id]))
            ->willReturn(collect([
                $product1, $product2
            ]));

        $fallbackFactoryMock = $this->createMock(FallbackFactory::class);
        $fallbackFactoryMock
            ->method('createDefaultComposition')
            ->willReturn($fallbackCompositionMock);

        $recommender = new RecommenderService($clientMock, $fallbackFactoryMock, 'http://base.url');
        $products = $recommender->recommend($segment, $proposer);
    }

    /** @test */
    public function it_works()
    {
        $this->signIn();

        $segment = create('Segment');

        $product1 = create('Product');
        $photo = new ProductPhoto;
        $photo->product_id = $product1->id;
        $photo->image_path = 'path';
        $photo->user_id = $this->signedInUser->id;
        $photo->save();

        $segmentProduct1 = create('SegmentProduct', 1, ['priority_id' => 1, 'product_id' => $product1->id, 'segment_id' => $segment->id]);

        $recommendations = create('Recommendation', 1, [
            'segment_id'    => $segment->id,
            'product_id'    => $product1
        ]);
        $proposer = create('Proposer', 1, [
            'max_item_number' => 5
        ]);
        $proposerItems = create('ProposerItem', 2, [
            'proposer_id'   => $proposer->id,
            'type_id'       => 3,    // html
            'product_id'    => $product1
        ]);

        Cache::shouldReceive('get')
            ->times(3)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $clientMock = $this->createMock(HttpClient::class);

        $recommender = $this->createService($clientMock);
        $products = $recommender->recommend($segment, $proposer);

        $this->assertCount(1, $products);
        $this->assertEquals($product1->id, $products[0]['id']);
    }

    /** @test */
    public function it_returns_a_unique_list_of_duplicated_excludable_product_ids()
    {
        $p1 = create('Product');
        $p2 = create('Product');
        $p3 = create('Product');

        $customProducts = [
            'always'    => [$p1, $p2],
            'optional'  => []
        ];

        $proposer = create('Proposer');
        $proposerItem1 = create('ProposerItem', 1, [
            'proposer_id' => $proposer->id, 'type_id' => 3, 'product_id' => $p2->id
        ]);
        $proposerItem2 = create('ProposerItem', 1, [
            'proposer_id' => $proposer->id, 'type_id' => 3, 'product_id' => $p3->id
        ]);

        $proposerItem1->type_key = 'product';
        $proposerItem2->type_key = 'product';

        $clientMock = $this->createMock(HttpClient::class);
        $recommender = $this->createService($clientMock);

        $ids = $this->invokeMethod($recommender, 'getExcludedProductIds', [$customProducts, collect([$proposerItem1, $proposerItem2])]);

        $this->assertEquals([$p2->id, $p3->id, $p1->id], $ids);
    }

    /** @test */
    public function it_returns_recommendations_and_segment_with_appearance_template()
    {
        $template = new SegmentAppearanceTemplate;
        $template->name = 'Template';
        $template->css_template = 'xy';
        $template->save();

        $segment = create('Segment', 1, ['template_id' => $template->id]);
        $proposer = create('Proposer');

        $serviceMock = $this->createMock(RecommenderService::class);
        $serviceMock->method('waitForSegment')
            ->willReturn($segment);

        $mockRecommendations = [
            ['id' => 1, 'name' => 'first', 'type_key' => 'product'],
            ['id' => 2, 'name' => 'second', 'type_key' => 'product']
        ];
        $serviceMock->method('recommend')
            ->willReturn($mockRecommendations);

        $this->app->instance(RecommenderService::class, $serviceMock);

        $response = $this->get('/api/recommendation/cookie-id/' . $proposer->slug);
        $response->assertStatus(200);

        $data = json_decode($response->content(), true);
        $this->assertEquals($mockRecommendations, $data['items']);
        $this->assertEquals($segment->id, $data['segment']['id']);
        $this->assertEquals($template->id, $data['segment']['template_id']);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $clientMock
     * @return RecommenderService
     */
    protected function createService(\PHPUnit\Framework\MockObject\MockObject $clientMock): RecommenderService
    {
        return new RecommenderService($clientMock, new FallbackFactory, 'http://base.url');
    }
}
