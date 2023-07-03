@extends('sendportal::layouts.app')

@section('title', __('Split Segment'))

@section('heading')
    {{ __('Segments') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Split Segment'))

        @slot('cardBody')
            {!! Form::open(['route' => ['sendportal.segments.store'], 'class' => 'form-horizontal']) !!}

            @include('sendportal::segments.partials.form')

            {!! Form::submitButton(__('Save')) !!}

            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
