@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Inaktív partnerek</h2>
        @forelse($partners as $partner)
            <li class="list-group-item">
                {{ $partner->name }}

                @can('restore', $partner)
                    <form METHOD="POST" action="{{ $partner->path('restore') }}" style="display: inline;">
                        {{ csrf_field() }}
                        {{ method_field('PATCH') }}

                        <button type="submit" class="btn btn-primary">Aktiválás</button>
                    </form>
                @endcan
            </li>
        @empty
            <p>Nincsenek partnerek</p>
        @endforelse
    </div>
@endsection
