<?php

namespace Sendportal\Base\Http\Controllers\Subscribers;



use Sendportal\Base\Jobs\ProcessImportJob;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;

use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Services\Subscribers\ImportSubscriberService;



use Sendportal\Base\Http\Requests\CsvImportRequest;
use Sendportal\Base\Http\Contact;
use App\CsvData;
use Sendportal\Base\Http\Requests\SubscribersImportRequest;
use Illuminate\Http\Request;
use Sendportal\Base\Http\Controllers\Controller;

use Rap2hpoutre\FastExcel\FastExcel;

class MatchSubscribersImportController extends Controller
{


/** @var SubscriberTenantRepositoryInterface */
    protected $segments;

protected $subscriberService;

    /** @var SegmentTenantRepository */
    private $segmentRepository;



    public function __construct(
        SegmentTenantRepository $segments,
        ImportSubscriberService $subscriberService,
        SegmentTenantRepository $segmentRepository
    )

    {
        $this->segments = $segments;
        $this->subscriberService = $subscriberService;
        $this->segmentRepository = $segmentRepository;

}

    public function getImport()
    {
        return view('import');
    }

    public function parseImport(SubscribersImportRequest $request)
    {
        
       $path = $request->file('file')->getRealPath();

       $new_segment=$request->new_segment;
       
 
       $segments_array=$request->segments;

       $workspace = auth()->user()->currentWorkspace();

if (!empty($segments_array)){

       $segments_names_array = array_map(function ($segment_id) use ($workspace) {
    return  $this->segments->findBy($workspace->id, 'id', $segment_id)->name;
}, $segments_array);


       $segments_names=implode(',',$segments_names_array);
} else {   $segments_names=NULL;

}



/** @var SubscriberTenantRepositoryInterface */




     $request->merge(['header' => true]);


$data = array_map('str_getcsv', file($path));


        if (count($data) > 0) {
                $csv_header_fields = [];
                foreach ($data[0] as $key => $value) {
                    $csv_header_fields[] = $value;
            }
            $csv_data = array_slice($data, 1,10);

    CsvData::truncate(); ///delete csv_data table content

        $csv_data_file = CsvData::create([
                'csv_filename' => $request->file('file')->getClientOriginalName(),
                'csv_header' => $request->has('header'),
                'csv_data' => json_encode($data)
            ]);
        } else {
            return redirect()->back();
        }



        return view('sendportal::subscribers.import_fields', compact( 'csv_header_fields', 'csv_data', 'csv_data_file','segments_names','segments_array','new_segment'));

    }


    public function processImport(Request $request)
    {




$request = new Request([
    'csv_data_file_id' => $request->csv_data_file_id,
    '_token' => $request->_token,
    'segments_array' => $request->segments_array,
    'new_segment' => $request->new_segment,
    'fields' => $request->fields,
]);

// it is to avoid closures


$workspace_id =  auth()->user()->currentWorkspace()->id;


$segments=  $this->segments ;
$subscriberService=  $this->subscriberService ;
$segmentRepository=  $this->segmentRepository;


    // ...
    // Dispatch the job to the Redis queue
    ProcessImportJob::dispatch($request,$workspace_id,$segments,$subscriberService, $segmentRepository);

    // ...


 return redirect()->route('sendportal.segments.index');


    }




    public function processImport_sync(Request $request)
    {
        $data = CsvData::find($request->csv_data_file_id);






        $csv_data = json_decode($data->csv_data, true);

        $pattern_email = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';


     $counter = [
                'created' => 0,
                'updated' => 0
            ];


        $workspace = auth()->user()->currentWorkspace();

        $token=$request->_token;

$segments_array=$request->segments_array;

$segments_array=json_decode($segments_array,true);

$new_segment=json_decode($request->new_segment,true);



        foreach ($csv_data as $row) {

$data_row=array() ;



 if ($row === reset($csv_data)) {

$first_row=$row;
    // Skip the first row
    continue;
  } 



foreach ($request->fields as $index => $field) {

$field_database=config('app.db_fields')[$field];


                if ($field_database=='ignore') {
    // Do nothing because column ignored
               } elseif($field_database=='segments')  {

$segment_row= $row[$index];
$all_segments_row=array();


$delimiter = ",";
$segment_row = explode(",", $segment_row);

foreach ($segment_row as $segm) {

if (!empty(trim($segm))) {

try{
   $segment_id = $this->segments->findBy($workspace->id, 'name', $segm)->id ;
} catch (Exception $e) {

$newsegm = array(
    "_token" => $token,
    "name" => $segm
);
   

     $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $newsegm  );
   $segment_id = $this->segments->findBy($workspace->id, 'name', $segm)->id ;


            }

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

try{
$email = $data_row['email'];
preg_match($pattern_email, $email, $matches);
$data_row['email']=$matches[0];
}  catch (Exception $e) {

        error_log('An error occurred during subscriber import: ' . $e->getMessage());
// exception handling
}

$subscriber= $this->subscriberService->import(auth()->user()->currentWorkspace()->id, $data_row);
}


 if ($subscriber->wasRecentlyCreated) {
                    $counter['created']++;



if (!empty($new_segment)){

if ($counter['created']==1) {


$new_segm = array(
    "_token" => $token,
    "name" => $new_segment
);


$new_segment_stored=  $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $new_segm);


$new_segment_stored =array($new_segment_stored->id);




} else {}




if (empty($data_row['segments'])) {
  $data_row['segments'] = $new_segment_stored;
} else {
$data_row['segments'] = array_merge($data_row['segments'], $new_segment_stored);
}


if (!empty($data_row['email'])) {
$subscriber= $this->subscriberService->import(auth()->user()->currentWorkspace()->id, $data_row);
}

}



                } else {
                    $counter['updated']++;
 


               }

        }
$counter['total']=$counter['created']+$counter['updated'];

 return redirect()->route('sendportal.subscribers.index')
                ->with('success', __('Imported :count subscribers, :created created and :updated updated', ['count' => $counter['total'],'created'=> $counter['created'], 'updated'=>$counter['updated']  ]));

    }

}

