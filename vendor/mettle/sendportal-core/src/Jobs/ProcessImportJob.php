<?php


namespace Sendportal\Base\Jobs;


use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Services\Subscribers\ImportSubscriberService;

use Sendportal\Base\Http\Controllers\Subscribers\MatchSubscribersImportController;



use App\CsvData;


use Illuminate\Support\Facades\Log;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
//use Illuminate\Queue\SerializesModels;
use Exception;

class ProcessImportJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable;


    public $timeout = 7000;

    protected $request;
    protected $workspace_id;


/** @var SubscriberTenantRepositoryInterface */
    protected $segments;

protected $subscriberService;

    /** @var SegmentTenantRepository */
    private $segmentRepository;


    public function __construct(
        $request,
        $workspace_id,
        SegmentTenantRepository $segments,
        ImportSubscriberService $subscriberService,
        SegmentTenantRepository $segmentRepository
    )

    {
        $this->request = $request;
        $this->workspace_id=$workspace_id;
        $this->segments = $segments;
        $this->subscriberService = $subscriberService;
        $this->segmentRepository = $segmentRepository;
        $this->onQueue('sendportal-imports'); // Set the job to the 'sendportal-imports' queue



}






//    protected $request;
  //  protected $workspace_id;
    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
 //   public function __construct($request, $workspace_id)
  //  {
      //  $this->request = $request;
    //    $this->onQueue('sendportal-imports'); // Set the job to the 'sendportal-imports' queue
   //     $this->workspace_id=$workspace_id;
 //   }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


     //   ini_set('max_execution_time', 6000); //3 minutes


     //   set_time_limit(6000); // Set the maximum execution time to 10 minutes (600 seconds)


        $data = CsvData::find($this->request->csv_data_file_id);
        $csv_data = json_decode($data->csv_data, true);
        $pattern_email = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';

        $counter = [
            'created' => 0,
            'updated' => 0
        ];


//        $workspace = auth()->user()->currentWorkspace();

     //   $workspace_id = $this->request->workspace_id;

        $token = $this->request->_token;
        $segments_array = $this->request->segments_array;
        $segments_array = json_decode($segments_array, true);
        $new_segment = json_decode($this->request->new_segment, true);

        foreach ($csv_data as $row) {


        sleep(0.1);


            $data_row = array();

            if ($row === reset($csv_data)) {
                $first_row = $row;
                // Skip the first row
                continue;
            }

            foreach ($this->request->fields as $index => $field) {
                $field_database = config('app.db_fields')[$field];

                if ($field_database == 'ignore') {
                    // Do nothing because column ignored
                } elseif ($field_database == 'segments') {
                    $segment_row = $row[$index];
                    $all_segments_row = array();

                    $delimiter = ",";
                    $segment_row = explode(",", $segment_row);




foreach ($segment_row as $segm) {
    if (!empty(trim($segm))) {


//        $segment = $this->segments->findBy($workspace->id, 'name', $segm);
        $segment = $this->segments->findBy($this->workspace_id, 'name', $segm);
//     $segment = $this->segments->findBy(auth()->user()->currentWorkspace()->id,  'name', $segm);



        if ($segment) {
            $segment_id = $segment->id;
        } else {
            $newsegm = array(
                "_token" => $token,
                "name" => $segm
            );
            try {
//                $newSegment = $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $newsegm);
                $newSegment = $this->segmentRepository->store($this->workspace_id, $newsegm);
 
               $segment_id = $newSegment->id;
            } catch (Exception $e) {
                Log::error('An error occurred while creating a new segment: ' . $e->getMessage());
                continue;
            }
        }
        array_push($all_segments_row, strval($segment_id));
    }
}




if (!empty($all_segments_row)){
if (empty($data_row[$field_database])) {
  $data_row[$field_database] = $all_segments_row;
} else {
$data_row[$field_database] = array_merge($data_row[$field_database], $all_segments_row);
}
}





} elseif($field_database=='unsubscribe_event_id')  {

$title_row= $first_row[$index];
$subscribe_row= $row[$index];



if (stripos($title_row, 'unsubscr') !== false) {
// we assume we are with sendportal//

if (!empty($subscribe_row)){
 $data_row[$field_database] = $row[$index];
$time = date('Y-m-d H:i:s');
$data_row['unsubscribed_at']=$time;

   }

} else{




if(strtolower($subscribe_row)==strtolower('false')) {

// it means we have subscribed column from mailbluster //


$data_row[$field_database] = '3';
$time = date('Y-m-d H:i:s');
$data_row['unsubscribed_at']=$time;

} elseif(strtolower($subscribe_row)==strtolower('true'))  {



$data_row[$field_database] = NULL;
$data_row['unsubscribed_at']=NULL;


}  else{}



}
                } else {


    $data_row[$field_database] = $row[$index];
                }
            }


// only the last field is taken into account, not all of them

if (!empty($segments_array)){
if (empty($data_row['segments'])) {
  $data_row['segments'] = $segments_array;
} else {
$data_row['segments'] = array_merge($data_row['segments'], $segments_array);
}
}

if (empty($data_row['segments'])){

$data_row['segments']=array();


}


if (!empty($data_row['email'])) {
$email = $data_row['email'];
if (preg_match($pattern_email, $email, $matches)){
$data_row['email']=$matches[0];
$subscriber= $this->subscriberService->import($this->workspace_id, $data_row);
} else {
        // Move on to the next row
        continue;
    }
}



 if ($subscriber->wasRecentlyCreated) {
                    $counter['created']++;



if (!empty($new_segment)){

if ($counter['created']==1) {


$new_segm = array(
    "_token" => $token,
    "name" => $new_segment
);


//  $new_segment_stored=  $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $new_segm);
$new_segment_stored=  $this->segmentRepository->store($this->workspace_id, $new_segm);


$new_segment_stored =array($new_segment_stored->id);


array_push($all_segments_row,strval($segment_id));
}
}




if (!empty($all_segments_row)){
if (empty($data_row[$field_database])) {
  $data_row[$field_database] = $all_segments_row;
} else {
$data_row[$field_database] = array_merge($data_row[$field_database], $all_segments_row);
}
}





} elseif($field_database=='unsubscribe_event_id')  {

$title_row= $first_row[$index];
$subscribe_row= $row[$index];



if (stripos($title_row, 'unsubscr') !== false) {
// we assume we are with sendportal//

if (!empty($subscribe_row)){
 $data_row[$field_database] = $row[$index];
$time = date('Y-m-d H:i:s');
$data_row['unsubscribed_at']=$time;

   }

} else{




if(strtolower($subscribe_row)==strtolower('false')) {

// it means we have subscribed column from mailbluster //


$data_row[$field_database] = '3';
$time = date('Y-m-d H:i:s');
$data_row['unsubscribed_at']=$time;

} elseif(strtolower($subscribe_row)==strtolower('true'))  {



$data_row[$field_database] = NULL;
$data_row['unsubscribed_at']=NULL;


}  else{}



}
                } else {


    $data_row[$field_database] = $row[$index];

           }
            }


// only the last field is taken into account, not all of them

if (!empty($segments_array)){
if (empty($data_row['segments'])) {
  $data_row['segments'] = $segments_array;
} else {
$data_row['segments'] = array_merge($data_row['segments'], $segments_array);
}
}

if (empty($data_row['segments'])){

$data_row['segments']=array();


}


if (!empty($data_row['email'])) {
$email = $data_row['email'];

if (preg_match($pattern_email, $email, $matches)) {
$data_row['email']=$matches[0];
// $subscriber= $this->subscriberService->import(auth()->user()->currentWorkspace()->id, $data_row);
$subscriber= $this->subscriberService->import($this->workspace_id, $data_row);
  } else { 

$data_row['email']='';
}

}




 if ($subscriber->wasRecentlyCreated) {
                    $counter['created']++;



if (!empty($new_segment)){

if ($counter['created']==1) {


$new_segm = array(
    "_token" => $token,
    "name" => $new_segment
);

//   $new_segment_stored=  $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $new_segm);

$new_segment_stored=  $this->segmentRepository->store($this->workspace_id, $new_segm);


$new_segment_stored =array($new_segment_stored->id);




} else {}




if (empty($data_row['segments'])) {
  $data_row['segments'] = $new_segment_stored;
} else {
$data_row['segments'] = array_merge($data_row['segments'], $new_segment_stored);
}


if (!empty($data_row['email'])) {
//  $subscriber= $this->subscriberService->import(auth()->user()->currentWorkspace()->id, $data_row);

$subscriber= $this->subscriberService->import($this->workspace_id, $data_row);


}

}



                } else {
                    $counter['updated']++;



               }


}


        }
//  $counter['total']=$counter['created']+$counter['updated'];

