@extends('sendportal::layouts.app')

@section('title', __('Segments'))

@section('heading')
    {{ __('Segments') }}
@endsection

@section('content')
    @component('sendportal::layouts.partials.actions')

        @slot('right')

 <a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.segments.create_derive') }}">
                <i class="fa fa-plus"></i> {{ __('Derive Segment') }}
            </a>



            <a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.segments.create_split') }}">
                <i class="fa fa-plus"></i> {{ __('Split Segment') }}
            </a>

<a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.segments.create') }}">
                <i class="fa fa-plus"></i> {{ __('New Segment') }}
            </a>

        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table">
            <table  id="segments-table"   class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Subscribers') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($segments as $segment)
                    <tr>
                        <td>
                            <a href="{{ route('sendportal.segments.view_segment', $segment->id) }}">
                                {{ $segment->name }}
                            </a>
                        </td>
                        <td>{{ $segment->subscribers_count }}</td>
                        <td>
                            @include('sendportal::segments.partials.actions')
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('You have not created any segments.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.25/b-2.0.1/b-colvis-2.0.1/b-html5-2.0.1/b-print-2.0.1/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.25/b-2.0.1/b-colvis-2.0.1/b-html5-2.0.1/b-print-2.0.1/datatables.min.js"></script>


<script>
$(document).ready(function() {
    $('#segments-table').DataTable({
        "order": [[ 1, "desc" ]]
    });
});
</script>



