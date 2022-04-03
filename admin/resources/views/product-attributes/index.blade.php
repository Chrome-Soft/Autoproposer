@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Termék attribútumok</h2>
        <list collection-name="product-attributes"></list>
        {{--<ul class="li.st-group">--}}
            {{--@forelse($attributes as $attribute)--}}
                {{--<li class="list-group-item">--}}
                    {{--<a href="{{ $attribute->path() }}">{{ $attribute->name }}</a>--}}
                {{--</li>--}}
            {{--@empty--}}
                {{--<p>Nincsenek termék attribútumok</p>--}}
            {{--@endforelse--}}
        {{--</ul>--}}
    </div>
@endsection
