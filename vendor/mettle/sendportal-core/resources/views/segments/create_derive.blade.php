@extends('sendportal::layouts.app')


@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.min.css">
@endpush


@section('title', __('Derive Segment'))

@section('heading')
    {{ __('Segments') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
          @slot('cardHeader', __('Select Subscribers in'))

        @slot('cardBody')
            {!! Form::open(['route' => ['sendportal.segments.derive_store'], 'class' => 'form-horizontal']) !!}


            <div class="form-group row form-group-subscribers">
                <label for="id-field-subscribers" class="control-label col-sm-3">{{ __('Any of the Segments') }}</label>
                <div class="col-sm-9">
                    {!! Form::select('segments[]', $segments, null, ['multiple' => true]) !!}

                </div>
            </div>



 <div class="form-group row form-group-subscribers">
                <label for="id-field-subscribers" class="control-label col-sm-3">{{ __('but not in any of the Segments') }}</label>
                <div class="col-sm-9">
                    {!! Form::select('non_segments[]', $segments, null, ['multiple' => true]) !!}

                </div>
            </div>



   {!! Form::textField('segment_name', __('Segment Name') ) !!}



            {!! Form::submitButton(__('Derive')) !!}

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

  <script>
        $('select[name="non_segments[]"]').selectize({
            plugins: ['remove_button']
        });
    </script>

@endpush




