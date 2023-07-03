<?php

namespace Sendportal\Base\Services\Campaigns;

use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;



class CampaignStatisticsService
{
    /**
     * @var CampaignTenantRepositoryInterface
     */
    protected $campaigns;

    public function __construct(CampaignTenantRepositoryInterface $campaigns, MessageTenantRepositoryInterface $messageRepo
 )
    {
        $this->campaigns = $campaigns;
        $this->messageRepo = $messageRepo;
    }

    /**
     * @throws Exception
     */
    public function getForCampaign(Campaign $campaign, Workspace $workspace): Collection
    {
        return $this->get(collect([$campaign]), $workspace);
    }

    /**
     * @throws Exception
     */
    public function getForCollection(Collection $campaigns, Workspace $workspace): Collection
    {
        return $this->get($campaigns, $workspace);
    }

    /**
     * @throws Exception
     */
    public function getForPaginator(LengthAwarePaginator $paginator, Workspace $workspace): Collection
    {
        return $this->get(collect($paginator->items()), $workspace);
    }

    /**
     * @throws Exception
     */
    protected function get(Collection $campaigns, Workspace $workspace): Collection
    {
        $countData = $this->campaigns->getCounts($campaigns->pluck('id'), $workspace->id);

// collect count for complaints
//        $messages = $this->messageRepo->complaints(auth()->user()->currentWorkspace()->id, Campaign::class, $campaign->id)->complained_at->count();


   //  $bounces_messages_id_failures= $this->messageRepo->bounces(auth()->user()->currentWorkspace()->id, Campaign::class, 39)->pluck('failures');
// 39 is $campaign->id with hard bounce


//  $count_permanent_failures = $bounces_messages_id_failures->flatten()->filter(function ($failure) {
//    return $failure->severity === 'Permanent';
//})->count();


//  $permanent_failures= $this->messageRepo->bounces(auth()->user()->currentWorkspace()->id, Campaign::class, 39)->pluck('failures')->flatten()->filter(function ($failure) {
  //  return $failure->severity === 'Permanent';
//  })->count();




//$count = $bounces_messages_id_failures->filter(function($failure) {
  //  return $failure->severity == 'Permanent';})->count();


//                       @forelse($messages as $message)
//dd($bounces_messages_id->pluck('failures')); // ->pluck('severity'));




//{dd($mess_id->failures);}
//    @foreach($message->failures as $failure)
  //                              {{ $failure->severity }}
    //                            @endforeach



        return $campaigns->map(function (Campaign $campaign) use ($countData) {
            return [
                'campaign_id' => $campaign->id,
                'counts' => [
                    'total' => $countData[$campaign->id]->total,
                    'open' => $countData[$campaign->id]->opened,
                    'click' => $countData[$campaign->id]->clicked,
                    'sent' =>  $campaign->formatCount($countData[$campaign->id]->sent) ,
                    'complained' => $this->messageRepo->complaints(auth()->user()->currentWorkspace()->id, Campaign::class, $campaign->id)->count(),  //  ->complained_at->count(),

                ],
                'ratios' => [
                    'open' => $campaign->getActionRatio($countData[$campaign->id]->opened, $countData[$campaign->id]->sent),
                    'hard_bounce' =>  $campaign->getActionRatio(   $this->messageRepo->bounces(auth()->user()->currentWorkspace()->id, Campaign::class, $campaign->id)->pluck('failures')->flatten()->filter(function ($failure) {
    return $failure->severity === 'Permanent';
})->count(),
   $countData[$campaign->id]->sent),
                    'bounce' => $campaign->getActionRatio($countData[$campaign->id]->bounced, $countData[$campaign->id]->sent),
                    'click' => $campaign->getActionRatio($countData[$campaign->id]->clicked, $countData[$campaign->id]->sent),
                ],
            ];
        })->keyBy('campaign_id');
    }
}
