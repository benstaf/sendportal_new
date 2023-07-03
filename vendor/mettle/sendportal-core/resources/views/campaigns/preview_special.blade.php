@extends('sendportal::layouts.app')

@section('title', __('Confirm Campaign'))

@section('heading')
    {{ __('Preview Special Campaign') }}: {{ $campaign->name }}
@stop

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header card-header-accent">
                <div class="card-header-inner">
                </div>
            </div>
            <div class="card-body">
                <form class="form-horizontal">
                    <div class="row">
                        <label class="col-sm-2 col-form-label">{{ __('From') }}:</label>
                        <div class="col-sm-10">
                            <b>
                                <span class="form-control-plaintext">{{ $campaign->from_name . ' <' . $campaign->from_email . '>' }}</span>
                            </b>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{ __('Subject') }}:</label>
                        <div class="col-sm-10">
                            <b>
                                <span class="form-control-plaintext">{{ $campaign->subject }}</span>
                            </b>
                        </div>
                    </div>

   <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{ __('Recipients') }}:</label>
                        <div class="col-sm-10">
                            <b>
                               	<span class="form-control-plaintext">{{ $campaign->segments->pluck("name")->implode(',') }}</span>
                            </b>
                        </div>
                    </div>

   <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{ __('Schedule') }}:</label>
                        <div class="col-sm-10">
                            <b>
                               	<span class="form-control-plaintext">{{ $campaign->scheduled_at }}</span>
                            </b>
                       	</div>
                    </div>



                    <div style="border: 1px solid #ddd; height: 600px">
                        <iframe id="js-template-iframe" srcdoc="{{ $campaign->merged_content }}" class="embed-responsive-item" frameborder="0" style="height: 100%; width: 100%"></iframe>
                    </div>

                </form>
            </div>
        </div>

    </div>

    <div class="col-md-4  text-center ">


        {!! Form::model($campaign, array('method' => 'PUT', 'route' => ['sendportal.campaigns.send_special', $campaign->id])) !!}


                <div class="form-group row form-group-schedule">
<!--
        <div>
            <a href="{{ route('sendportal.campaigns.index') }}" class="btn btn-light">{{ __('Back to main menu') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Send campaign') }}</button>


        </div>
-->
                </div>

            </div>

        <div>
            <a href="{{ route('sendportal.campaigns.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Send campaign') }}</button>
        </div>

        {!! Form::close() !!}

    </div>


</div>

@stop

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        var target = $('.segments-container');
        $('#id-field-recipients').change(function() {
            if (this.value == 'send_to_all') {
                target.addClass('hide');
            } else {
                target.removeClass('hide');
            }
        });

        var element = $('#input-field-scheduled_at');
        $('#id-field-schedule').change(function() {
            if (this.value == 'now') {
                element.addClass('hide');
            } else {
                element.removeClass('hide');
            }
        });

        $('#input-field-scheduled_at').flatpickr({
            enableTime: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i",
        });
    </script>
@endpush
