@extends('sendportal::layouts.app')


@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.min.css">
@endpush


@section('title', __('Split Segment'))

@section('heading')
    {{ __('Segments') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Split Segment'))

        @slot('cardBody')
            {!! Form::open(['route' => ['sendportal.segments.split_store'], 'class' => 'form-horizontal']) !!}


            <div class="form-group row form-group-subscribers">
                <label for="id-field-subscribers" class="control-label col-sm-3">{{ __('Segments') }}</label>
                <div class="col-sm-9">
                    {!! Form::select('segments[]', $segments, null, ['multiple' => true]) !!}

                </div>
            </div>

   <div class="form-group row form-group-num-segments">
                <label for="id-field-num-segments" class="control-label col-sm-3">{{ __('Number of slices') }}</label>
                <div class="col-sm-9">
                    {!! Form::number('num_segments', null, ['class' => 'form-control','min' => 1 ]) !!}
                </div>
            </div>



            {!! Form::submitButton(__('Split')) !!}

            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"></script>

    <script>
        $('select[name="segments[]"]').selectize({
            plugins: ['remove_button']
        });
    </script>
@endpush




