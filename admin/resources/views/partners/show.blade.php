@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <view-details :details="{{ json_encode($partner->getViewData()) }}" type="Partner"></view-details>
            </div>
            <div class="col-md-6">
                <h4>Külső partner azonosító</h4>
                {{ $partner->external_id }}
                <h4>Partner API kulcs</h4>
                {{ $partner->apiKey->key }}
                <hr>
                <strong>FONTOS</strong>
                <p>Az itt megjelenített külső partner azonosító és API kulcs feltétlenül szükséges ahhoz, hogy a partner weboldalába integrálható legyen az ajánló rendszer!</p>
            </div>
        </div>
        <div class="row" style="padding-top: 20px">
            <div class="col-md-12">
                @can('delete', $partner)
                    <form METHOD="POST" action="{{ $partner->path() }}" style="display: inline;">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}

                        <button type="submit" class="btn btn-primary btn-danger confirmed">Inaktiválás</button>
                    </form>
                @endcan

                @can('update', $partner)
                    <a href="{{ $partner->path('edit') }}" class="btn btn-primary">Szerkesztés</a>
                @endcan

                <a href="{{ url('partners')  }}" class="btn btn-link">Vissza</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <pages :partner="{{ json_encode($partner) }}"></pages>
            </div>
        </div>
    </div>
@endsection
