<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers;




use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SpecCampaignStoreRequest;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\EmailServiceTenantRepository;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;
use Sendportal\Base\Repositories\TemplateTenantRepository;
use Sendportal\Base\Services\Campaigns\CampaignStatisticsService;
use Sendportal\Base\Models\CampaignStatus;

class Spec2Controller extends Controller
{
    /** @var CampaignTenantRepositoryInterface */
    protected $campaigns;

    /** @var TemplateTenantRepository */
    protected $templates;

    /** @var SegmentTenantRepository */
    protected $segments;

    /** @var EmailServiceTenantRepository */
    protected $emailServices;

    /** @var SubscriberTenantRepositoryInterface */
    protected $subscribers;

    /**
     * @var CampaignStatisticsService
     */
    protected $campaignStatisticsService;

    public function __construct(
        CampaignTenantRepositoryInterface $campaigns,
        TemplateTenantRepository $templates,
        SegmentTenantRepository $segments,
        EmailServiceTenantRepository $emailServices,
        SubscriberTenantRepositoryInterface $subscribers,
        CampaignStatisticsService $campaignStatisticsService
    ) {
        $this->campaigns = $campaigns;
        $this->templates = $templates;
        $this->segments = $segments;
        $this->emailServices = $emailServices;
        $this->subscribers = $subscribers;
        $this->campaignStatisticsService = $campaignStatisticsService;
    }






    /**
     * @throws Exception
     */
    public function index(): ViewContract
    {
        $workspace = auth()->user()->currentWorkspace();
        $campaigns = $this->campaigns->paginate($workspace->id, 'created_atDesc', ['status']);

        return view('sendportal::campaigns.index', [
            'campaigns' => $campaigns,
            'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspace),
        ]);
    }



   /**
     * @throws Exception
     */
    public function create_special_camp(): ViewContract
    {
        $templates = [null => '- None -'] + $this->templates->pluck(auth()->user()->currentWorkspace()->id);
        $emailServices = $this->emailServices->all(auth()->user()->currentWorkspace()->id);
        $segments = $this->segments->pluck(auth()->user()->currentWorkspace()->id);

        return view('sendportal::campaigns.create_special_view', compact('templates', 'emailServices','segments'));
    }




    /**
     * @throws Exception
     */
    public function create(): ViewContract
    {
        $templates = [null => '- None -'] + $this->templates->pluck(auth()->user()->currentWorkspace()->id);
        $emailServices = $this->emailServices->all(auth()->user()->currentWorkspace()->id);

        return view('sendportal::campaigns.create', compact('templates', 'emailServices'));
    }



    /**
     * @throws Exception
     */
    public function store_special(SpecCampaignStoreRequest $request): RedirectResponse
    {



$validrequest=$request->validated();
unset($validrequest['segments']);
        $campaign = $this->campaigns->store(auth()->user()->currentWorkspace()->id, $this->handleCheckboxes($validrequest));



//    determine recipients
// 2 cases: 1. no excluded domains. or 2. excluded domains

$excluded_emails=$request->get('exclude_domains');

if ($excluded_emails !== null) {
$excluded_emails = explode(',', $excluded_emails);
}

if (empty($excluded_emails)) {   
        $campaign->segments()->sync($request->get('segments'));
} else {
// 2nd case, with excluded domains

$excluded_domains=[];

foreach ($excluded_emails as $email) { 
$partials =explode('@', $email);
$domain=end($partials);

$domain = str_replace(" ", "", $domain);

$excluded_domains[]=$domain;
}



$excluded_domains=array_unique($excluded_domains);


$segments=$request->get('segments');

$segments_name=[];
$subscribers=[];
if(!empty($segments)){
foreach ($segments as $segmentId) {
    $segment = $this->segments->find(auth()->user()->currentWorkspace()->id, $segmentId);
    $segmentSubscribers = $segment->subscribers;
    $subscribers = array_merge($subscribers, $segmentSubscribers->all());
    $segments_name[]= $segment->name;     

}
}

// $unique_subscribers=array_unique($subscribers);


$unique_subscribers = [];
foreach ($subscribers as $subscriber) {
    $email = $subscriber->email;
    $unique_subscribers[$email] = $subscriber;
}








$subscribers_included=[];
foreach ($unique_subscribers as $element1) {
    $found = false;
    foreach ($excluded_domains as $element2) {
if (strpos($element1->email, $element2) !== false) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $subscribers_included[] = $element1;
    }
}



    $token=$request->get('_token');

    $name_campaign= $request->get('name')  ;
    $parts = explode(' ', $name_campaign);
    $name_campaign=$parts[0];
    $name_segment= $name_campaign . "_" .  implode('_',$segments_name) ;

    $data_segment = [    '_token' => $token,    'name' => $name_segment];

$segment_included  =  $this->segments->store(auth()->user()->currentWorkspace()->id,$data_segment);



if(!empty($subscribers_included)){
   // loop through the diff array and add each element to the new diff segment
    for ($i = 0; $i < count($subscribers_included); $i++) {
        $segment_included->subscribers()->attach($subscribers_included[$i]);
}
}



    //  $this->segmentRepository->findBy(auth()->user()->currentWorkspace()->id, 'name',$name_segment);


        $campaign->segments()->sync($segment_included);



}

        $scheduledAt = $request->get('schedule') === 'scheduled' ? Carbon::parse($request->get('scheduled_at')) : now();

 $campaign->update([
            'scheduled_at' => $scheduledAt,
            'status_id' => CampaignStatus::STATUS_DRAFT,
            'save_as_draft' => $request->get('behaviour') === 'draft',
        ]);

         
        return redirect()->route('sendportal.campaigns.preview_special', $campaign->id);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): ViewContract
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id);

        return view('sendportal::campaigns.show', compact('campaign'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id);
        $emailServices = $this->emailServices->all(auth()->user()->currentWorkspace()->id);
        $templates = [null => '- None -'] + $this->templates->pluck(auth()->user()->currentWorkspace()->id);

        return view('sendportal::campaigns.edit', compact('campaign', 'emailServices', 'templates'));
    }

    /**
     * @throws Exception
     */
    public function update(int $campaignId, CampaignStoreRequest $request): RedirectResponse
    {
        $campaign = $this->campaigns->update(
            auth()->user()->currentWorkspace()->id,
            $campaignId,
            $this->handleCheckboxes($request->validated())
        );

        return redirect()->route('sendportal.campaigns.preview', $campaign->id);
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function preview(int $id)
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id);
        $subscriberCount = $this->subscribers->countActive(auth()->user()->currentWorkspace()->id);

        if (!$campaign->draft) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $segments = $this->segments->all(auth()->user()->currentWorkspace()->id, 'name');

        return view('sendportal::campaigns.preview', compact('campaign', 'segments', 'subscriberCount'));
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function status(int $id)
    {
        $workspace = auth()->user()->currentWorkspace();
        $campaign = $this->campaigns->find($workspace->id, $id, ['status']);

        if ($campaign->sent) {
            return redirect()->route('sendportal.campaigns.reports.index', $id);
        }

        return view('sendportal::campaigns.status', [
            'campaign' => $campaign,
            'campaignStats' => $this->campaignStatisticsService->getForCampaign($campaign, $workspace),
        ]);
    }

    /**
     * Handle checkbox fields.
     *
     * NOTE(david): this is here because the Campaign model is marked as being unable to use boolean fields.
     */
    private function handleCheckboxes(array $input): array
    {
        $checkboxFields = [
            'is_open_tracking',
            'is_click_tracking'
        ];

        foreach ($checkboxFields as $checkboxField) {
            if (!isset($input[$checkboxField])) {
                $input[$checkboxField] = false;
            }
        }

        return $input;
    }
}
