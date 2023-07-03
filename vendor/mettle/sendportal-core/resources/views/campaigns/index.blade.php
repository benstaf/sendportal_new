@extends('sendportal::layouts.app')  




@section('title', __('Campaigns'))

@section('heading')
    {{ __('Campaigns') }}
@endsection

@section('content')



  <div class="row mb-4">
        <div class="col-12">
            <div class="card">



<!--    @component('sendportal::layouts.partials.actions')  -->

        @slot('right')


 <a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.SpecialCampaigns') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('Special Campaign') }}
            </a>

            <a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.campaigns.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Campaign') }}
            </a>
        @endslot
<!--  @endcomponent  -->


    <div class="card-body">
        <div class="card-table"  style="max-width: 100%; overflow: auto;"  >




            <table id="example"  class="table-striped">
                <thead>

                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Recipients') }}</th>
                    <th>{{ __('Sent') }}</th>
                    <th style="white-space: nowrap;"  >{{ __('Send time') }}</th>
                    <th>{{ __('Opened') }}</th>
                    <th style="white-space: nowrap;"  >{{ __('Hard Bounced') }}</th>
                    <th>{{ __('Bounced') }}</th>
                    <th>{{ __('Complained') }}</th>
                    <th>{{ __('Clicked') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($campaigns as $campaign)
                    <tr>
                        <td>
                            @if ($campaign->draft)
                                <a href="{{ route('sendportal.campaigns.edit', $campaign->id) }}">{{ $campaign->name }}</a>
                            @elseif($campaign->sent)
                                <a href="{{ route('sendportal.campaigns.reports.index', $campaign->id) }}">{{ $campaign->name }}</a>
                            @else
                                <a href="{{ route('sendportal.campaigns.status', $campaign->id) }}">{{ $campaign->name }}</a>
                            @endif
                        </td>


<td>
    @foreach ($campaign->segments as $segment)
        <a href="{{ route('sendportal.segments.view_segment', $segment->id) }}">{{ $segment->name }}</a>
        @unless($loop->last)
            ,
        @endunless
    @endforeach
</td>

                        <td class="text-center"  >{{ $campaignStats[$campaign->id]['counts']['sent'] }}</td>
                        <td style="white-space: nowrap;"  ><span title="{{ $campaign->scheduled_at }}">{{optional($campaign->scheduled_at)->__toString() }}</span></td>



                        <td class="text-center"  >{{ number_format($campaignStats[$campaign->id]['ratios']['open'] * 100, 1) . '%' }}</td>
                        <td class="text-center"  >{{ number_format($campaignStats[$campaign->id]['ratios']['hard_bounce'] * 100, 1) }}%</td>
                        <td class="text-center" >{{ number_format($campaignStats[$campaign->id]['ratios']['bounce'] * 100, 1) }}%</td>
                        <td class="text-center"  >{{ $campaignStats[$campaign->id]['counts']['complained'] }}</td>
                        <td class="text-center" >{{ number_format($campaignStats[$campaign->id]['ratios']['click'] * 100, 1) . '%' }}</td>
                        <td><span title="{{ $campaign->created_at }}">{{ $campaign->created_at->format('j F Y H:i')  }}</span></td>
                        <td>
                            @include('sendportal::campaigns.partials.status')
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @if ($campaign->draft)
                                        <a href="{{ route('sendportal.campaigns.edit', $campaign->id) }}"
                                           class="dropdown-item">
                                            {{ __('Edit') }}
                                        </a>
                                    @else
                                        <a href="{{ route('sendportal.campaigns.reports.index', $campaign->id) }}"
                                           class="dropdown-item">
                                            {{ __('View Report') }}
                                        </a>
                                    @endif

                                    <a href="{{ route('sendportal.campaigns.duplicate', $campaign->id) }}"
                                       class="dropdown-item">
                                        {{ __('Duplicate') }}
                                    </a>


                                    @if ($campaign->draft)
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('sendportal.campaigns.destroy.confirm', $campaign->id) }}"
                                           class="dropdown-item">
                                            {{ __('Delete') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('You have not created any campaigns.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>


    @include('sendportal::layouts.partials.pagination', ['records' => $campaigns])


        </div>
    </div>
 </div>
   



@endsection


<!-- Include DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/colvis/1.1.0/css/dataTables.colVis.min.css">




<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>


<script type="text/javascript">
    var $j = jQuery.noConflict();





   $j(document).ready(function() {
  //      moment.locale('en');
    //   moment.updateLocale('en', {
      //     datetimeFormat: 'MM D YYYY HH:mm'       });

 // $j.fn.dataTable.moment( 'MMM D YYYY HH:mm' );


        // Initialize DataTables
        var table = $j('#example').DataTable({
            responsive: true,
            "lengthMenu": [[100, 200, -1], [100, 200, "All"]], // Set the lengthMenu option
            colReorder: true, // Enable colReorder extension
            order: [[3, 'desc']],
            dom: 'lBfrtipR',
            buttons: [
            'colvis'
        ],

  columnDefs: [ { targets: [5,8,9,10], visible: false }] // hide columns 2 and 3 by default

        });

        });
</script>

