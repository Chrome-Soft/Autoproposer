@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Ez a szolgáltatás jelenleg nem elérhető</h2>
        @if (isset($exception) && !empty($exception->getMessage()))
            <p>{{ $exception->getMessage() }}</p>
        @else
            <p>Kérjük próbáld meg a következő frissítés után.</p>
        @endif
    </div>
@endsection