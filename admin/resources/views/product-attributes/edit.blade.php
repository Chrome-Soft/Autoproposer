@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><h2>Termék attribútum szerkesztése</h2></div>

                    <div class="panel-body">
                        <form method="POST" action="{{ $productAttribute->path() }}">
                            @method('PATCH')

                            @include('product-attributes.form', [
                                'buttonText'        => 'Mentés'
                            ])

                            <a href="{{ $productAttribute->path() }}" class="btn btn-link">Mégsem</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
