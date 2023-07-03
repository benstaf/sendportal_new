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

class SplitStoreJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable;


    public $timeout = 7000;

    protected $request;
//    protected $workspace_id;


/** @var SubscriberTenantRepositoryInterface */
    protected $segments;

protected $subscriberService;

    /** @var SegmentTenantRepository */
    private $segmentRepository;


    public function __construct(
        $request,
        $workspace_id,
//        SegmentTenantRepository $segments,
//        ImportSubscriberService $subscriberService,
        SegmentTenantRepository $segmentRepository
    )

    {
        $this->request = $request;
        $this->workspace_id=$workspace_id;
  //      $this->segments = $segments;
//        $this->subscriberService = $subscriberService;
        $this->segmentRepository = $segmentRepository;
        $this->onQueue('sendportal-split'); // Set the job to the 'sendportal-imports' queue



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



//public function split_store(SegmentsSplitRequest $request): RedirectResponse



    $token = $this->request->_token;

//    $token = $request->get('_token');
    $segments= $this->request->segments;
    $id=$segments[0];
 //   $id = $request->get('segments')[0];
//    $num_segments = $request->get('num_segments');
    $num_segments = $this ->request->num_segments;
//$this->workspace_id
    $segment = $this->segmentRepository->find($this->workspace_id, $id);

//    $segment = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $id);
    $subscribers = $segment->subscribers;
    $num_subscribers = count($subscribers);
    $name_split = $segment->name;

    // create n new segments
    for ($i = 1; $i <= $num_segments; $i++) {
        $name_split_i = $name_split . " #$i";
        $data_i = ['_token' => $token, 'name' => $name_split_i];
        $this->segmentRepository->store($this->workspace_id, $data_i);
    }

    // get the IDs of the new segments
    $new_segments = [];
    for ($i = 1; $i <= $num_segments; $i++) {
        $name_split_i = $name_split . " #$i";
        $segment_i = $this->segmentRepository->findBy( $this->workspace_id, 'name', $name_split_i);
        $new_segments[] = $segment_i->id;
    }

    // loop through the subscribers and assign them to each segment in a round-robin fashion
    for ($i = 0; $i < $num_subscribers; $i++) {
        $segment_id = $new_segments[$i % $num_segments];
        $segment = $this->segmentRepository->find($this->workspace_id, $segment_id);
        $segment->subscribers()->attach($subscribers[$i]);
        sleep(0.1); // Sleep for 0.1 second (adjust as needed)

    }
}
//    return redirect()->route('sendportal.segments.index');
}


