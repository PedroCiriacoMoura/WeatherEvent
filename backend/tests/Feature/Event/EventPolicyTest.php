<?php

namespace Tests\Feature\Event;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\RequiresSpatialDatabase;
use Tests\TestCase;

class EventPolicyTest extends TestCase
{
    use RefreshDatabase;
    use RequiresSpatialDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipWithoutSpatialSupport();
    }

    public function test_non_owner_cannot_view_event(): void
    {
        $event = Event::factory()->for(User::factory())->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/events/{$event->id}")->assertForbidden();
    }

    public function test_non_owner_cannot_update_event(): void
    {
        $event = Event::factory()->for(User::factory())->create();
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/events/{$event->id}", ['name' => 'Hijack'])->assertForbidden();
    }

    public function test_non_owner_cannot_delete_event(): void
    {
        $event = Event::factory()->for(User::factory())->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/events/{$event->id}")->assertForbidden();
    }

    public function test_owner_can_manage_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/events/{$event->id}")->assertOk();
        $this->deleteJson("/api/events/{$event->id}")->assertNoContent();
    }
}
