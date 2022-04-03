@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Termék adatok</h2>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped table-hover">
                            <tr scope="row">
                                <th scope="col">Név</th>
                                <td scope="col">{{ $product->name }}</td>
                            </tr>
                            <tr scope="row">
                                <th scope="col">Leírás</th>
                                <td scope="col">{{ $product->description }}</td>
                            </tr>
                            <tr scope="row">
                                <th scope="col">Hivatkozás</th>
                                <td scope="col"><a href="{{ $product->link }}" target="_blank">Link</a></td>
                            </tr>
                            @if ($product->attributes)
                                @foreach ($product->attributes as $attr)
                                    <tr scope="row">
                                        <th scope="col">
                                            {{ $attr->name }}
                                        </th>
                                        <td scope="col">
                                            @if (is_array($attr->pivot->value))
                                                {{ $attr->pivot->value[0] }} - {{ $attr->pivot->value[1] }}
                                            @else
                                                {{ $attr->pivot->value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            @foreach($product->prices as $price)
                                <tr scope="row">
                                    <th scope="col">
                                        {{ $price->currency->name }}
                                    </th>
                                    <td scope="col">
                                        {{ $price->price }} {{ $price->currency->symbol }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr scope="row">
                                <th scope="col">Létrehozva</th>
                                <td scope="col">{{ $product->created_at->diffForHumans() }}</td>
                            </tr>
                            <tr scope="row">
                                <th scope="col">Létrehozta</th>
                                <td scope="col">{{ $product->user->name }}</td>
                            </tr>
                            <tr scope="row">
                                <th scope="col">Összes megjelenés</th>
                                <td scope="col">{{ $present }}</td>
                            </tr>
                            <tr scope="row">
                                <th scope="col">Összes kattintás</th>
                                <td scope="col">{{ $view }}</td>
                            </tr>
                            <tr scope="row">
                                <th scope="col">Kattintás%</th>
                                <td scope="col">{{ $product->getViewRatio($present, $view) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @if (!$product->photos->isEmpty())
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <img src="{{ optional($product->mediumPhoto)->public_path }}" alt="photo">
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
        @endif
        <div class="row" style="padding-top: 20px">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                    @can('update', $product)
                        <form METHOD="POST" action="{{ $product->path() }}" style="display: inline;">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}

                            <button type="submit" class="btn btn-primary btn-danger confirmed">Törlés</button>
                        </form>

                        <a href="{{ $product->path('edit') }}" class="btn btn-primary">Szerkesztés</a>
                    @endcan

                    <a href="{{ url('products')  }}" class="btn btn-link">Vissza</a>
            </div>
            <div class="col-md-4"></div>

        </div>
    </div>
@endsection
