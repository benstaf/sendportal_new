<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Segments;


use Sendportal\Base\Jobs\SplitStoreJob;



use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SegmentRequest;
use Sendportal\Base\Http\Requests\SegmentsSplitRequest;
use Sendportal\Base\Http\Requests\SegmentsDeriveRequest;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;

class SegmentsController extends Controller
{
    /** @var SegmentTenantRepository */
    private $segmentRepository;


  /** @var SubscriberTenantRepository */
    private $subscriberRepo;




    public function __construct(SegmentTenantRepository $segmentRepository,SubscriberTenantRepositoryInterface $subscriberRepo)
    {
        $this->segmentRepository = $segmentRepository;
     	$this->subscriberRepo = $subscriberRepo;
    }



    /**
     * @throws Exception
     */
    public function index(): View
    {
        $segments = $this->segmentRepository->paginate(auth()->user()->currentWorkspace()->id, 'name',[],10000 );

        return view('sendportal::segments.index', compact('segments'));
    }

    public function create(): View
    {
        return view('sendportal::segments.create');
    }

    public function create_split(): View
    {
        $segments = $this->segmentRepository->pluck(auth()->user()->currentWorkspace()->id);

     	return view('sendportal::segments.create_split', compact('segments'));
    }

    public function create_derive(): View
    {
        $segments = $this->segmentRepository->pluck(auth()->user()->currentWorkspace()->id);

        $not_segments=$segments;
        return view('sendportal::segments.create_derive', compact('segments','not_segments'));
    }
    




    /**
     * @throws Exception
     */
    public function store(SegmentRequest $request): RedirectResponse
    {


        $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $request->all());

        return redirect()->route('sendportal.segments.index');
    }




public function split_store(SegmentsSplitRequest $request): RedirectResponse
{




$request = new SegmentsSplitRequest([
    '_token' => $request->_token,
    'segments' => $request->segments,
    'num_segments' => $request->num_segments
]);

// it is to avoid closures


$workspace_id =  auth()->user()->currentWorkspace()->id;


$segmentRepository=  $this->segmentRepository;



// Create a new instance of the SplitStoreJob

// Dispatch the job to the queue

SplitStoreJob::dispatch($request,$workspace_id, $segmentRepository);


// Return the appropriate response (e.g., a redirect response)
return redirect()->route('sendportal.segments.index');
}




public function split_store_sync(SegmentsSplitRequest $request): RedirectResponse
{
    $token = $request->get('_token');
    $id = $request->get('segments')[0];
    $num_segments = $request->get('num_segments');
    $segment = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $id);
    $subscribers = $segment->subscribers;
    $num_subscribers = count($subscribers);
    $name_split = $segment->name;

    // create n new segments
    for ($i = 1; $i <= $num_segments; $i++) {
        $name_split_i = $name_split . " #$i";
        $data_i = ['_token' => $token, 'name' => $name_split_i];
        $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $data_i);
    }

    // get the IDs of the new segments
    $new_segments = [];
    for ($i = 1; $i <= $num_segments; $i++) {
        $name_split_i = $name_split . " #$i";
        $segment_i = $this->segmentRepository->findBy(auth()->user()->currentWorkspace()->id, 'name', $name_split_i);
        $new_segments[] = $segment_i->id;
    }

    // loop through the subscribers and assign them to each segment in a round-robin fashion
    for ($i = 0; $i < $num_subscribers; $i++) {
        $segment_id = $new_segments[$i % $num_segments];
        $segment = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $segment_id);
        $segment->subscribers()->attach($subscribers[$i]);
    }

    return redirect()->route('sendportal.segments.index');
}
















    /**
     * @throws Exception
     */
    public function split_store_2(SegmentsSplitRequest $request): RedirectResponse
    {
    
    $token=$request->get('_token');

    $id=  $request->get('segments')[0] ;
    $segment = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $id);

    $subscribers = $segment -> subscribers;


    $segments_subscriber=$subscribers[0]->segments ;


    $name_split= $segment -> name ;

    $name_split_1 = $name_split . " #1";
    $name_split_2 = $name_split	. " #2";

    $data_1 = [    '_token' => $token,    'name' => $name_split_1];
    $data_2 = [    '_token' => $token,    'name' => $name_split_2];

    $this->segmentRepository->store(auth()->user()->currentWorkspace()->id,$data_1);
    $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $data_2);

    $segment_1 =  $this->segmentRepository->findBy(auth()->user()->currentWorkspace()->id, 'name',$name_split_1);
    $segment_2 =  $this->segmentRepository->findBy(auth()->user()->currentWorkspace()->id, 'name',$name_split_2);


   // loop through the original array and add each element to a new array alternatively
    for ($i = 0; $i < count($subscribers); $i++) {
     if ($i % 2 == 0) {
        $segment_1->subscribers()->attach($subscribers[$i]);
      } else {
        $segment_2->subscribers()->attach($subscribers[$i]);
  }
}



        return redirect()->route('sendportal.segments.index');
    }


//    */
    public function derive_store(SegmentsDeriveRequest $request): RedirectResponse
    {

$segments = $request->get('segments');
$subscribers = [];


if(!empty($segments)){
foreach ($segments as $segmentId) {
    $segment = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $segmentId);
    $segmentSubscribers = $segment->subscribers;
    $subscribers = array_merge($subscribers, $segmentSubscribers->all());
}
}

$unique_subscribers=array_unique($subscribers);


$non_segments = $request->get('non_segments');
$subscribers_non = [];


if(!empty($non_segments)){
foreach ($non_segments as $segmentId) {
    $segment_non = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $segmentId);
    $segmentSubscribers_non = $segment_non->subscribers;
    $subscribers_non = array_merge($subscribers_non, $segmentSubscribers_non->all());

}

$unique_subscribers_non=array_unique($subscribers_non);
// $subscribers_diff=array_diff($unique_subscribers,$unique_subscribers_non);


$subscribers_diff=[];
foreach ($unique_subscribers as $element1) {
    $found = false;
    foreach ($unique_subscribers_non as $element2) {
        if ($element1->hash === $element2->hash) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $subscribers_diff[] = $element1;
    }
}



} else{  

$subscribers_diff=$unique_subscribers;
}





//dd($unique_subscribers[5]->email,$unique_subscribers_non[0]->email);



    $token=$request->get('_token');

    $name_segment= $request->get('segment_name') ;


    $data_segment = [    '_token' => $token,    'name' => $name_segment];

$segment_diff  =  $this->segmentRepository->store(auth()->user()->currentWorkspace()->id,$data_segment);

    //  $this->segmentRepository->findBy(auth()->user()->currentWorkspace()->id, 'name',$name_segment);

if(!empty($subscribers_diff)){
   // loop through the diff array and add each element to the new diff segment
    for ($i = 0; $i < count($subscribers_diff); $i++) {
        $segment_diff->subscribers()->attach($subscribers_diff[$i]);
}
}

$id_diff=strval($segment_diff->id);

        return redirect()->route('sendportal.segments.view_segment', ['id' => $id_diff] );
    }





    /**
     * @throws Exception
     */
    public function edit(int $id, SubscriberTenantRepositoryInterface $subscriberRepository): View
    {
        $segment = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $id, ['subscribers']);

        return view('sendportal::segments.edit', compact('segment'));
    }



    /**
     * @throws Exception
     */
    public function view_segment(int $id, SubscriberTenantRepositoryInterface $subscriberRepo): View
    {
    $segment = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $id, ['subscribers']);
    $subscribers = $segment->subscribers()->paginate(50);

    return view('sendportal::segments.view_subscribers', compact('segment', 'subscribers'));
    }



    /**
     * @throws Exception
     */
    public function update(int $id, SegmentRequest $request): RedirectResponse
    {
        $this->segmentRepository->update(auth()->user()->currentWorkspace()->id, $id, $request->all());

        return redirect()->route('sendportal.segments.index');
    }

    /**
     * @throws Exception
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->segmentRepository->destroy(auth()->user()->currentWorkspace()->id, $id);

        return redirect()->route('sendportal.segments.index');
    }
}
