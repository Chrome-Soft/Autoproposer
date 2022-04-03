@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><h2>Szegmens létrehozása</h2></div>

                    <div class="panel-body">
                        <form method="POST" action="/segments">
                            @include('segments.form', [
                                'segment'       => new \App\Segment,
                                'criterias'     => $criterias
                            ])

                            <a href="/segments" class="btn btn-link">Mégsem</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
