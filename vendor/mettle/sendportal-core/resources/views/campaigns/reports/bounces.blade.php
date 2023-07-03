
@extends('sendportal::layouts.app')

@section('title', $campaign->name)

@section('heading', $campaign->name)

@section('content')

    @include('sendportal::campaigns.reports.partials.nav')

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table"  id="messages-table"  >
                <thead>
                <tr>
                    <th>{{ __('Subscriber') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Bounced') }}</th>
                </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td><a href="{{ route('sendportal.subscribers.show', $message->subscriber_id) }}">{{ $message->recipient_email }}</a></td>
                      


 <td>

                                @foreach($message->failures as $failure)
                                {{ $failure->severity }}
                                @endforeach
                            </td>



      <td>
                                {{ $message->subject }}

                                @foreach($message->failures as $failure)
                                    <div class="mt-2 color-gray-500">
                                        {{ $failure->failed_at }}&nbsp;:&nbsp;{{ $failure->severity }}&nbsp;-&nbsp;{{ $failure->description }}
                                    </div>
                                @endforeach
                            </td>



                            <td>{{ \Sendportal\Base\Facades\Helper::displayDate($message->bounced_at->format('M j, Y H:i')   ) }}</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <p class="empty-table-text">{{ __('There are no subscribers') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('sendportal::layouts.partials.pagination', ['records' => $messages])

@endsection


<!-- DataTable CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.dataTables.min.css">

<!-- DataTable JS -->

<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>


<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/colreorder/1.6.2/js/dataTables.colReorder.min.js"></script>




<script type="text/javascript">
    var $j = jQuery.noConflict();




<script>
$j(document).ready(function() {
    $j('#messages-table').DataTable({

 "lengthMenu": [[100, 200, -1], [100, 200, "All"]], // Set the lengthMenu option
            colReorder: true, // Enable colReorder extension
            order: [[1, 'desc']],
            dom: 'lBfrtipR',
            buttons: [
            'colvis'
        ],




});
});
</script>

