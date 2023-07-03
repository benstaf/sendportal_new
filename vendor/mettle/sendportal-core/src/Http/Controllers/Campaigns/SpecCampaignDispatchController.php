<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SpecCampaignDispatchRequest;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

class SpecCampaignDispatchController extends Controller
{
    /** @var CampaignTenantRepositoryInterface */
    protected $campaigns;

    /**
     * @var QuotaServiceInterface
     */
    protected $quotaService;

    public function __construct(
        CampaignTenantRepositoryInterface $campaigns,
        QuotaServiceInterface $quotaService
    ) {
        $this->campaigns = $campaigns;
        $this->quotaService = $quotaService;
    }

    /**
     * Dispatch the campaign.
     *
     * @throws Exception
     */
    public function send_special(SpecCampaignDispatchRequest $request, int $id): RedirectResponse
    {



        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id, ['email_service']);

        if ($campaign->status_id !== CampaignStatus::STATUS_DRAFT) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        if (!$campaign->email_service_id) {
            return redirect()->route('sendportal.campaigns.edit', $id)
                ->withErrors(__('Please select an Email Service'));
        }


        if ($this->quotaService->exceedsQuota($campaign)) {
            return redirect()->route('sendportal.campaigns.edit', $id)
                ->withErrors(__('The number of subscribers for this campaign exceeds your SES quota'));
        }


        $campaign->update([
            'status_id' => CampaignStatus::STATUS_QUEUED,
            'save_as_draft' => false,
        ]);

        return redirect()->route('sendportal.campaigns.index', $id); 
//        return redirect()->route('sendportal.campaigns.status', $id);  
 
    }
}
