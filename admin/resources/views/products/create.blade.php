@extends('layouts.app')

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js" defer></script>
    <script type="text/javascript" src="{{ URL::asset('js/product.js') }}" defer></script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><h2>Termék létrehozása</h2></div>

                    <div class="panel-body">
                        <form method="POST" action="/products" enctype="multipart/form-data">
                            @include('products.form', [
                                'product'       => new \App\Product(),
                                'buttonText'    => 'Létrehozás'
                            ])

                            <a href="/products" class="btn btn-link">Mégsem</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
