@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Hiba történt</h2>
        @if (isset($exception) && !empty($exception->getMessage()))
            <p>{{ $exception->getMessage() }}</p>
        @else
            <p>Valamilyen hiba történt a szerveren a kérés feldolgozása során. Kérjük próbáld meg később.</p>
        @endif
    </div>
@endsection