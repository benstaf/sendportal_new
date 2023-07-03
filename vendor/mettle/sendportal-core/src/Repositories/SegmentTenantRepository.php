<?php

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Models\Segment;
use Illuminate\Support\Facades\DB;



class SegmentTenantRepository extends BaseTenantRepository
{
    /**
     * @var string
     */
    protected $modelName = Segment::class;

    /**
     * {@inheritDoc}
     */
    public function update($workspaceId, $id, array $data)
    {
        $instance = $this->find($workspaceId, $id);

        $this->executeSave($workspaceId, $instance, $data);

        return $instance;
    }

    /**
     * Sync subscribers
     *
     * @param Segment $segment
     * @param array $subscribers
     * @return array
     */
    public function syncSubscribers(Segment $segment, array $subscribers = [])
    {
        return $segment->subscribers()->sync($subscribers);
    }


/**
     * Bulk insert subscribers into segments.
     *
     * @param array $data
     * @return void
     */
    public function bulkAttachSubscribers(array $data)
    {
        \DB::table('segment_subscriber')->insert($data);
    }




    /**
     * {@inheritDoc}
     */
    public function destroy($workspaceId, $id): bool
    {
        $instance = $this->find($workspaceId, $id);

        $instance->subscribers()->detach();
        $instance->campaigns()->detach();

        return $instance->delete();
    }
}
