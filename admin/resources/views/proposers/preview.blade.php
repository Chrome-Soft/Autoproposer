@extends('layouts.preview')

@section('content')
    <proposer-preview :proposer="{{ json_encode($proposer) }}" :cookie-id="{{ json_encode($cookieId) }}" :items="{{ json_encode($items) }}" :track-interactions="{{ json_encode($trackInteractions) }}" :storage-url="{{ \Illuminate\Support\Facades\Storage::url('') }}"></proposer-preview>
@endsection