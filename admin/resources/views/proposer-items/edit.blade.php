@extends('layouts.app')

@section('scripts')
    <script src="https://cloud.tinymce.com/5/tinymce.min.js" defer></script>
    <script type="text/javascript" src="{{ URL::asset('js/proposer-item.js') }}" defer></script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><h2>Proposer item szerkesztése</h2></div>

                    <div class="panel-body">
                        <form method="POST" action="{{ $proposerItem->path() }}" enctype="multipart/form-data">
                            @method('PATCH')
                            @include('proposer-items.form', [
                                'proposerItem'  => $proposerItem,
                                'buttonText'    => 'Mentés',
                                'proposer'      => $proposer
                            ])

                            <a href="{{ $proposerItem->proposer->path() }}" class="btn btn-link">Mégsem</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
