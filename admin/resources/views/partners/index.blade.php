@extends('layouts.app')

@section('content')
    <div class="container">
        @if ($deleted == 0)
            <h2>Aktív partnerek</h2>
            <list collection-name="partners" :default-filters="{{ json_encode([$defaultFilter]) }}"></list>
        @else
            <h2>Inaktív partnerek</h2>
            <list collection-name="partners" :default-filters="{{ json_encode([$defaultFilter]) }}" :custom-actions="{{ json_encode($customActions) }}"></list>
        @endif
    </div>
@endsection
