@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Inaktív szegmensek</h2>
        @forelse($segments as $segment)
            <li class="list-group-item">
                {{ $segment->name }}

                @can('restore', $segment)
                    <form METHOD="POST" action="{{ $segment->path('restore') }}" style="display: inline;">
                        {{ csrf_field() }}
                        {{ method_field('PATCH') }}

                        <button type="submit" class="btn btn-primary">Aktiválás</button>
                    </form>
                @endcan
            </li>
        @empty
            <p>Nincsenek szegmensek</p>
        @endforelse
    </div>
@endsection
