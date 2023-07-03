@extends('sendportal::layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.min.css">

@endpush

@section('heading')
    {{ __('Import Subscribers') }}
@stop

@section('content')


    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Import via CSV file'))

        @slot('cardBody')
            <p><b>{{ __('Max CSV file size: 40 Mb') }}</b></p>


            {!! Form::open(['route' => ['sendportal.subscribers.import_parse'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}



            {!! Form::fileField('file', 'File', ['required' => 'required']) !!}


{!! Form::textField('new_segment', __('New segment for new subscribers')) !!}




            <div class="form-group row form-group-subscribers">
                <label for="id-field-subscribers" class="control-label col-sm-3">{{ __('Existing Segments') }}</label>
                <div class="col-sm-9">
                    {!! Form::select('segments[]', $segments, null, ['multiple' => true]) !!}
                </div>

                </div>




            {!! Form::submitButton(__('Upload')) !!}

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
