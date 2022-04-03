@extends('layouts.app')

@section('content')
<proposer-show inline-template :proposer="{{ json_encode($proposer) }}">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <view-details :details="{{ json_encode($proposer->getViewData()) }}" type="Proposer"></view-details>
            </div>
        </div>
        <div class="row" style="padding-top: 20px">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                    @can('update', $proposer)
                        <form METHOD="POST" action="{{ $proposer->path() }}" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}

                            <button type="submit" class="btn btn-primary btn-danger confirmed">Törlés</button>
                        </form>
                        <a href="{{ $proposer->path('edit') }}" class="btn btn-primary">Szerkesztés</a>
                    @endcan

                    <a v-if="isIframe()" class="btn btn-primary" href="{{ $proposer->path('preview') }}" target="_blank">Előnézet</a>
                    <button v-if="isIframe()" class="btn btn-primary" @click.prevent="onCopyClick()">Másolás</button>

                    <a href="{{ url('proposers')  }}" class="btn btn-link">Vissza</a>
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <proposer-items :proposer="{{ $proposer }}" :can-update="{{ Auth::user()->can('update', $proposer) == true ? 'true' : 'false' }}"></proposer-items>
            </div>
        </div>
    </div>
</proposer-show>
@endsection