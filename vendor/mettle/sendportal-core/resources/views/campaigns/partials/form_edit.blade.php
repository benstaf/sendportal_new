
@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.min.css">

 <style>
        /* add your custom CSS rules here */
.hide {
    display: none;
},
        #is_open_tracking,
        #is_click_tracking {
            display: none;
        }

    </style>

@endpush




{!! Form::textField('name', __('Campaign Name')) !!}

            <div class="form-group row form-group-subscribers">
                <label for="id-field-subscribers" class="control-label col-sm-3">{{ __('Recipients') }}</label>
                <div class="col-sm-9">
                    {!! Form::select('segments[]', $segments,$campaign->segments->pluck('id')->toArray(), ['multiple' => true]) !!}

                </div>
            </div>

{!! Form::textField('exclude_domains', __('Exclude Recipients with Domains')) !!}
{!! Form::textField('subject', __('Email Subject')) !!}
{!! Form::textField('from_name', __('From Name')) !!}
{!! Form::textField('from_email', __('From Email')) !!}

@if ($emailServices->count() === 1)
    {!! Form::hidden('email_service_id', $emailServices->first()->id) !!}
@else
    {!! Form::selectField('email_service_id', __('Email Service'), $emailServices->pluck('name', 'id'), isset($campaign->email_service_id) ? $campaign->email_service_id : null) !!}
@endif

{!! Form::checkboxField('is_open_tracking', __('Track Opens'), 1, $campaign->is_open_tracking ?? 1, ['class' => 'hide']) !!}
{!! Form::checkboxField('is_click_tracking', __('Track Clicks'), 1, $campaign->is_click_tracking ?? 1, ['class' => 'hide'] ) !!}

{!! Form::textareaField('content', __('Content')) !!}

                <div class="pb-2"><b>{{ __('Sending Options') }}</b></div>
                <div class="form-group row form-group-schedule">
                    <div class="col-sm-12">
                        <select id="id-field-schedule" class="form-control" name="schedule">
                            <option value="now" {{ old('schedule') === 'now'}}>
                                {{ __('Send Now') }}
                            </option>
                            <option value="scheduled" {{ old('schedule') === 'now' }}>
                                {{ __('Send Later') }}
                            </option>
                        </select>
                    </div>


</div>
<div class="form-group row" id="scheduled-at-container" style="display:none;">
    <div class="col-sm-12">
        <input id="input-field-scheduled_at" class="form-control" name="scheduled_at" type="text" value="Select date and time">
    </div>
</div>





<div class="form-group row">
    <div class="offset-sm-3 col-sm-9">




        <a href="{{ route('sendportal.campaigns.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary">{{ __('Save and Preview') }}</button>
    </div>
</div>

{!! Form::close() !!}

@include('sendportal::layouts.partials.summernote')




@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('js')




    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"></script>

    <script>
        $('select[name="segments[]"]').selectize({
            plugins: ['remove_button']
        });
    </script>



    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
	var target = $('.segments-container');
        $('#id-field-recipients').on('change touchstart',function() {
            if (this.value == 'send_to_all') {
                target.addClass('hide');
            } else {
                target.removeClass('hide');
            }
	});

	var element = $('#scheduled-at-container');
        $('#id-field-schedule').on('change touchstart',function() {
            if (this.value == 'now') {
                element.hide();
            } else {
                element.show();
            }
	});

	$('#input-field-scheduled_at').flatpickr({
            enableTime: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i",
        });
    </script>
@endpush




