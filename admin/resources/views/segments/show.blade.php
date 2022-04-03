@extends('layouts.app')

@section('content')
<segment-show inline-template :segment="{{ json_encode($segment) }}">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>{{ $segment->name }}</h2>
                    </div>

                    <div class="panel-body">
                        <p>{{ $segment->description }}</p>
                        <p>Sablon: {{ optional($segment->appearance_template)->name ?? 'Nincs sablon' }}</p>
                        @if ($segment->appearance_template)
                            <view-appearance-template :template="{{ json_encode($segment->appearance_template) }}" :storage-url="{{ json_encode(asset('images')) }}"></view-appearance-template>
                        @endif

                        Létrehozva {{ $segment->created_at->diffForHumans() }}
                        {{ $segment->user->name }} által
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="padding: 20px 0">
            <div class="col-md-12">
                @can('delete', $segment)
                    <form METHOD="POST" action="{{ $segment->path() }}" style="display: inline;">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}

                        <button type="submit" class="btn btn-primary btn-danger confirmed">Inaktiválás</button>
                    </form>
                @endcan

                @can('update', $segment)
                    <a href="{{ $segment->path('edit') }}" class="btn btn-primary">Szerkesztés</a>
                @endcan

                @can('segmentify', $segment)
                    <button class="btn btn-primary" @click.prevent="onSegmentifyClick">Szegmentálás</button>
                @endcan

                @can('update', $segment)
                    <button class="btn btn-primary" @click.prevent="onReplicateClick">Másolás</button>
                @endcan

                <a href="{{ url('segments')  }}" class="btn btn-link">Vissza</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Kritériumok</h2>
                    </div>

                    <div class="panel-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col" style="width:20%">Csoport reláció</th>
                                    <th scope="col">Kritérium</th>
                                    <th scope="col">Reláció</th>
                                    <th scope="col">Érték</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($segment->groups as $i => $group)
                                    @foreach ($group->segment_group_criterias as $groupCriteria)
                                        <tr scope="row">
                                            <td scope="col">&nbsp;</td>
                                            <td scope="col">{{ $groupCriteria->criteria->name }}</td>
                                            <td scope="col">{{ $groupCriteria->relation->name }}</td>
                                            <td scope="col">{{ $groupCriteria->normalizedValue }}</td>
                                        </tr>
                                        @if ($groupCriteria->bool_type)
                                            <tr scope="row">
                                                <td scope="col">&nbsp;</td>
                                                <td scope="col" class="font-weight-bold">{{ $groupCriteria->getBoolTypeAsText() }}</td>
                                                <td scope="col">&nbsp;</td>
                                                <td scope="col">&nbsp;</td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    <tr scope="row">
                                        <td scope="col" class="font-weight-bold">{{ $group->getBoolTypeAsText() }}</td>
                                        <td scope="col">&nbsp;</td>
                                        <td scope="col">&nbsp;</td>
                                        <td scope="col">&nbsp;</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="padding-top: 20px">
            <div class="col-md-12">
                <segment-products
                        :segment="{{ json_encode($segment) }}"
                        :init-segment-products="{{ json_encode($segmentProducts) }}"
                        :priorities="{{ json_encode($priorities) }}"
                        :can-update="{{ Auth::user()->can('update', $segment) == true ? 'true' : 'false' }}">
                </segment-products>
            </div>
        </div>
        <div class="row" style="padding-top: 20px">
            <div class="col-md-12">
                <list collection-name="user-data" custom-api-endpoint="/api/segments/{{ $segment->slug }}/user-data"></list>
            </div>
        </div>
    </div>
</segment-show>
@endsection
