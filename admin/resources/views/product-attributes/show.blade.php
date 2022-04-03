@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <view-details :details="{{ json_encode($productAttribute->getViewData()) }}" type="Termék attribútum"></view-details>
            </div>
        </div>
        <div class="row" style="padding-top: 20px">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                    @can('update', $productAttribute)
                        <form METHOD="POST" action="{{ $productAttribute->path() }}" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}

                            <button type="submit" class="btn btn-primary btn-danger confirmed">Törlés</button>
                        </form>
                        <a href="{{ $productAttribute->path('edit') }}" class="btn btn-primary">Szerkesztés</a>
                    @endcan

                    <a href="/product-attributes" class="btn btn-link">Vissza</a>
            </div>
            <div class="col-md-4"></div>

        </div>
    </div>
@endsection
