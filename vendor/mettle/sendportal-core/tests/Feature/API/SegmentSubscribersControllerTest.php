<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class SegmentSubscribersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function the_index_gets_segment_subscribers()
    {
        $user = $this->createUserWithWorkspace();

        $segment = $this->createSegment($user);
        $subscriber = $this->createSubscriber($user);

        $segment->subscribers()->save($subscriber);

        $route = route('sendportal.api.segments.subscribers.index', [
            'workspaceId' => $user->currentWorkspace()->id,
            'segment' => $segment->id,
            'api_token' => $user->api_token,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => [
                Arr::only($subscriber->toArray(), ['first_name', 'last_name', 'email'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_subscriber_can_be_added_to_a_segment()
    {
        $user = $this->createuserwithworkspace();

        $segment = $this->createsegment($user);
        $subscriber = $this->createsubscriber($user);

        $route = route('sendportal.api.segments.subscribers.store', [
            'workspaceId' => $user->currentWorkspace()->id,
            'segment' => $segment->id,
        ]);

        $request = [
            'subscribers' => [$subscriber->id]
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $user->api_token]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $expected = [
            'data' => [
                Arr::only($subscriber->toArray(), ['first_name', 'last_name', 'email'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function the_store_endpoint_is_idempotent()
    {
        $user = $this->createuserwithworkspace();

        $segment = $this->createsegment($user);
        $existingSubscriber = $this->createsubscriber($user);

        $segment->subscribers()->attach($existingSubscriber);

        $newSubscriber = $this->createSubscriber($user);

        $route = route('sendportal.api.segments.subscribers.store', [
            'workspaceId' => $user->currentWorkspace()->id,
            'segment' => $segment->id,
        ]);

        $data = [
            'subscribers' => [$existingSubscriber->id, $newSubscriber->id]
        ];

        $this->post($route, array_merge($data, ['api_token' => $user->api_token]));

        $this->assertCount(2, $segment->refresh()->subscribers);
    }

    /** @test */
    public function a_segments_subscribers_can_be_synced()
    {
        $user = $this->createUserWithWorkspace();

        $segment = $this->createSegment($user);
        $oldSubscriber = $this->createSubscriber($user);
        $newSubscriber = $this->createSubscriber($user);

        $segment->subscribers()->save($oldSubscriber);

        $route = route('sendportal.api.segments.subscribers.update', [
            'workspaceId' => $user->currentWorkspace()->id,
            'segment' => $segment->id,
            'api_token' => $user->api_token,
        ]);

        $request = [
            'subscribers' => [$newSubscriber->id],
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $oldSubscriber->id,
        ]);

        $this->assertDatabaseHas('segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $newSubscriber->id,
        ]);

        $expected = [
            'data' => [
                Arr::only($newSubscriber->toArray(), ['first_name', 'last_name', 'email'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_segment_can_be_deleted()
    {
        $user = $this->createUserWithWorkspace();

        $segment = $this->createSegment($user);
        $subscriber = $this->createSubscriber($user);

        $segment->subscribers()->save($subscriber);

        $route = route('sendportal.api.segments.subscribers.destroy', [
            'workspaceId' => $user->currentWorkspace()->id,
            'segment' => $segment->id,
            'api_token' => $user->api_token,
        ]);

        $request = [
            'subscribers' => [$subscriber->id],
        ];

        $response = $this->delete($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }
}
