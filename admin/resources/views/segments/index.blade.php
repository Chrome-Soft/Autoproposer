@extends('layouts.app')

@section('content')
    <div class="container">
        @if ($deleted == 0)
            <h2>Aktív szegmensek</h2>
            <list collection-name="segments" :default-filters="{{ json_encode([$defaultFilter]) }}"></list>
        @else
            <h2>Inaktív szegmensek</h2>
            <list collection-name="segments" :default-filters="{{ json_encode([$defaultFilter]) }}" :custom-actions="{{ json_encode($customActions) }}"></list>
        @endif
    </div>
@endsection
