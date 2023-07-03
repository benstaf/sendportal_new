@extends('sendportal::layouts.app')

@section('title', __('Import Subscribers'))

@section('heading')
    {{ __('Import Subscribers') }}
@endsection

@section('content')


  <div class="row mb-4">
        <div class="col-12">
            <div class="card">



@if(!empty($segments_names))
                <div class="card-header card-header-accent">
                    <div class="card-header-inner">
                        {{ __('Additional Segments: ')}}{{ $segments_names }}
                    </div>
                </div>


@endif


@if(!empty($new_segment))

   <div class="card-header card-header-accent">
                    <div class="card-header-inner">
                        {{ __('New Segment to be created for New Subscribers:') }} {{ $new_segment }}
                    </div>
                </div>

@endif


 <form class="form-horizontal" method="POST" action="{{ route('sendportal.subscribers.import_process') }}">

                <div class="card-body">
                    <div style="width: 99%;">
                

<div class="card-table table-responsive"> <!-- Add container div with a fixed width -->

                            {{ csrf_field() }}
<input type="hidden" name="new_segment" value="{{json_encode($new_segment)}}">
<input type="hidden" name="segments_array" value="{{json_encode($segments_array)}}">
                            <input type="hidden" name="csv_data_file_id" value="{{ $csv_data_file->id }}" />
                            <table class="table">


                                <tr>
                                    @foreach ($csv_header_fields as $key => $value)
                                        <td>
                                            <select name="fields[{{ $key }}]">
                                                @foreach (config('app.db_fields') as $db_field)
                                                    <option value="{{ (\Request::has('header')) ? $db_field : $loop->index }}"
                    @if ((stripos($value, 'email') !== false && $db_field === 'email')||  (stripos($value, 'mails') !== false && $db_field === 'email')  ||  (stripos($value, 'tag') !== false && $db_field === 'segments')  ) selected @endif>{{ $db_field }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endforeach
                                </tr>


                                @if (isset($csv_header_fields))

                                <tr>
                                    @foreach ($csv_header_fields as $csv_header_field)
                                        <th>{{ $csv_header_field }}</th>
                                    @endforeach
                                </tr>
                                @endif
                                @foreach ($csv_data as $row)
                                    <tr>
                                    @foreach ($row as $key => $value)
                                        <td>{{ $value }}</td>
                                    @endforeach
                                    </tr>
                                @endforeach
                            </table>

</div>



    </div>



                </div>


<button type="submit" class="btn btn-primary">
                                Import Data
                            </button>
                        </form>


            </div>
        </div>
    </div>








@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"></script>


    <script>
        $('select[name="segments[]"]').selectize({
            plugins: ['remove_button']
        });
    </script>



@endpush
