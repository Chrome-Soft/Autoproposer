<?php

namespace Tests\Feature;

use App\PageLoad;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StorePageLoadTest extends TestCase
{
    use DatabaseMigrations, WithoutMiddleware, WithFaker;

    /** @test */
    public function sanitize_urls_if_too_long()
    {
        $partner = create('Partner' , 1, [
            'external_id'           =>  \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'is_anonymus_domain'    => false
        ]);

        $data = [
            'partnerId'     => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => 'http://from.url/hu/payment-result/globalpayments/return/JYS-V7N-Q09?device=web&OPERATION=CREATE_ORDER&ORDERNUMBER=253524869588183&MD=JYS-V7N-Q09&PRCODE=0&SRCODE=0&RESULTTEXT=OK&DIGEST=VjYBoyT78mr8bYZQAdM6I5KOtjPN96sw3RV%2BI1LPYuOypoQQoILha%2FZYb5pyN%2FcFtuO3da6Bw%2BIHU6eu1dzPzLuBxWc8SF1FJSrN1OaiQBCXj53uty%2BzMh7L%2Bzuu%2FjKM0tXnZ6og3oMywLujxZTS3jONVU73CYuN3O3EQvtt6jcuebMpgH64BchmMEjmKaUGjCoVYqBuAvgKBhM6LfQDzLClQsyhVEYjbAX%2B0JV4v%2Fik2B3rBGIF3rcSRJn885rZpRf%2BRpR43JEF18DyJgwwNh%2FvXcEDZwBgfXSO6kN4Otc4BHq3eHDndUFNxA%2FHaBZbHjrxOgY43B4ZbTddiB3MLg%3D%3D&DIGEST1=H0CnN43oprJ9ZJsj6tq3wIT4%2BOc1mkMvKsqq7r0kRsAS%2BqV0HUHAx%2F0YqiA1h%2BFvOiReifwn36dxBWlxQMWNl2ouOvvCtZD7rPyL8Kx2XEQhar0%2FBqUuSaCfs8Q%2BkEgKMlTsIlwdG627%2FSuMT%2FcBGBrS7%2Fopohfp6%2BQesK6DtGjVoJoitNtAXOBj98ZOtoEtxZkeAvzWGcEyWI4mGl5nGjKvtMnggObgUhMiXDoLZaUpYoZu5kvD%2FqUcbQAIyhIgvj5Mb1yfYVNSPLqRdcbr81anUwqqpBE8fwMasV8xCKEPBcTxzutKTFrsxbF%2F%2BW2JMcSs%2Bw85IL5781FM22ZKpQ%3D%3D',
            'toUrl'        => 'http://to.url/hu/payment-result/globalpayments/return/JYS-V7N-Q09?device=web&OPERATION=CREATE_ORDER&ORDERNUMBER=253524869588183&MD=JYS-V7N-Q09&PRCODE=0&SRCODE=0&RESULTTEXT=OK&DIGEST=VjYBoyT78mr8bYZQAdM6I5KOtjPN96sw3RV%2BI1LPYuOypoQQoILha%2FZYb5pyN%2FcFtuO3da6Bw%2BIHU6eu1dzPzLuBxWc8SF1FJSrN1OaiQBCXj53uty%2BzMh7L%2Bzuu%2FjKM0tXnZ6og3oMywLujxZTS3jONVU73CYuN3O3EQvtt6jcuebMpgH64BchmMEjmKaUGjCoVYqBuAvgKBhM6LfQDzLClQsyhVEYjbAX%2B0JV4v%2Fik2B3rBGIF3rcSRJn885rZpRf%2BRpR43JEF18DyJgwwNh%2FvXcEDZwBgfXSO6kN4Otc4BHq3eHDndUFNxA%2FHaBZbHjrxOgY43B4ZbTddiB3MLg%3D%3D&DIGEST1=H0CnN43oprJ9ZJsj6tq3wIT4%2BOc1mkMvKsqq7r0kRsAS%2BqV0HUHAx%2F0YqiA1h%2BFvOiReifwn36dxBWlxQMWNl2ouOvvCtZD7rPyL8Kx2XEQhar0%2FBqUuSaCfs8Q%2BkEgKMlTsIlwdG627%2FSuMT%2FcBGBrS7%2Fopohfp6%2BQesK6DtGjVoJoitNtAXOBj98ZOtoEtxZkeAvzWGcEyWI4mGl5nGjKvtMnggObgUhMiXDoLZaUpYoZu5kvD%2FqUcbQAIyhIgvj5Mb1yfYVNSPLqRdcbr81anUwqqpBE8fwMasV8xCKEPBcTxzutKTFrsxbF%2F%2BW2JMcSs%2Bw85IL5781FM22ZKpQ%3D%3D'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_loads', [
            'partner_external_id'   => $partner->external_id,
            'from_url'              => 'http://from.url/hu/payment-result/globalpayments/return/JYS-V7N-Q09',
            'to_url'                => 'http://to.url/hu/payment-result/globalpayments/return/JYS-V7N-Q09',
        ]);
    }

    /** @test */
    public function pass_urls_if_not_too_long()
    {
        $partner = create('Partner' , 1, [
            'external_id'           =>  \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'is_anonymus_domain'    => false
        ]);

        $data = [
            'partnerId'     => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => 'http://from.url/',
            'toUrl'        => 'http://to.url/'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_loads', [
            'partner_external_id'   => $partner->external_id,
            'from_url'              => 'http://from.url/',
            'to_url'                => 'http://to.url/',
        ]);
    }

    /** @test */
    public function without_url()
    {
        $partner = create('Partner' , 1, [
            'external_id'           =>  \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'is_anonymus_domain'    => false
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => null,
            'toUrl'        => 'http://to.url/'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_loads', [
            'partner_external_id'   => $partner->external_id,
            'from_url'              => null,
            'to_url'                => 'http://to.url/',
        ]);
    }

    /** @test */
    public function hide_domain_if_partner_has_anonymus_domain_set()
    {
        $partner = create('Partner' , 1, [
            'external_id'           =>  \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'is_anonymus_domain'    => true
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => 'http://from.url/page',
            'toUrl'        => 'http://to.url/other-page'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_loads', [
            'partner_external_id'   => $partner->external_id,
            'from_url'              => '/page',
            'to_url'                => '/other-page',
        ]);
    }

    /** @test */
    public function url_without_query_no_anonym()
    {
        $partner = create('Partner', 1, [
            'is_anonymus_domain'    => false
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => 'http://www.from.url/one-page',
            'toUrl'        => 'http://to.url/other-page'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $pageLoad = PageLoad::where('partner_external_id', $partner->external_id)->first();

        $this->assertEquals('http://www.from.url/one-page', $pageLoad->from_url);
        $this->assertEquals('http://to.url/other-page', $pageLoad->to_url);
    }

    // url query nélkül anonym
    public function url_without_query_anonym()
    {
        $partner = create('Partner', 1, [
            'is_anonymus_domain'    => true
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => 'http://www.from.url/',
            'toUrl'        => 'http://to.url/other-page'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $pageLoad = PageLoad::where('partner_external_id', $partner->external_id)->first();

        $this->assertEquals('/', $pageLoad->from_url);
        $this->assertEquals('/other-page', $pageLoad->to_url);
    }

    // url queryvel nem anonym
    public function url_with_query_no_anonym()
    {
        $partner = create('Partner', 1, [
            'is_anonymus_domain'    => false
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => 'http://www.from.url/one-page?foo=bar&baz=biz',
            'toUrl'        => 'http://www.to.url/one-page?foo=bar&baz=biz'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $pageLoad = PageLoad::where('partner_external_id', $partner->external_id)->first();

        $this->assertEquals('http://www.from.url/one-page', $pageLoad->from_url);
        $this->assertEquals('http://www.to.url/one-page', $pageLoad->to_url);
    }

    // url queryvel anonym
    public function url_with_query_anonym()
    {
        $partner = create('Partner', 1, [
            'is_anonymus_domain'    => true
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => 'http://www.from.url/one-page?foo=bar&baz=biz',
            'toUrl'        => 'http://www.to.url/other-page?foo=bar&baz=biz'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $pageLoad = PageLoad::where('partner_external_id', $partner->external_id)->first();

        $this->assertEquals('/one-page', $pageLoad->from_url);
        $this->assertEquals('/other-page', $pageLoad->to_url);
    }

    // nincs url anonym
    public function no_url_with_anonym()
    {
        $partner = create('Partner', 1, [
            'is_anonymus_domain'    => true
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => '',
            'toUrl'        => 'http://www.to.url/other-page?foo=bar&baz=biz'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $pageLoad = PageLoad::where('partner_external_id', $partner->external_id)->first();

        $this->assertEquals('', $pageLoad->from_url);
        $this->assertEquals('/other-page', $pageLoad->to_url);
    }

    // nincs url nem anonym
    public function no_url_with_no_anonym()
    {
        $partner = create('Partner', 1, [
            'is_anonymus_domain'    => false
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => null,
            'toUrl'        => 'http://www.to.url/other-page?foo=bar&baz=biz'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $pageLoad = PageLoad::where('partner_external_id', $partner->external_id)->first();

        $this->assertEquals('', $pageLoad->from_url);
        $this->assertEquals('/other-page', $pageLoad->to_url);
    }

    /** @test */
    public function regression__to_url_null_when_anonym_and_url_is_homepage()
    {
        $partner = create('Partner', 1, [
            'is_anonymus_domain'    => true
        ]);

        $data = [
            'partnerId'    => $partner->external_id,
            'cookieId'     => 'asdf',
            'fromUrl'      => null,
            'toUrl'        => 'https://www.budapestinfo.hu'
        ];

        $response = $this->post('/api/page-load', $data);
        $response->assertStatus(200);

        $pageLoad = PageLoad::where('partner_external_id', $partner->external_id)->first();

        $this->assertEquals('', $pageLoad->from_url);
        $this->assertEquals('/', $pageLoad->to_url);
    }
}
