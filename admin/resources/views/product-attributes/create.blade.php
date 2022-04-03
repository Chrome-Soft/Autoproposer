@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><h2>Termék attribútum létrehozása</h2></div>

                    <div class="panel-body">
                        <form method="POST" action="/product-attributes">
                            @include('product-attributes.form', [
                                'productAttribute'  => new \App\ProductAttribute,
                                'buttonText'        => 'Létrehozás'
                            ])

                            <a href="/product-attributes" class="btn btn-link">Mégsem</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
