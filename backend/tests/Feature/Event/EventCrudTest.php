<?php

namespace Tests\Feature\Event;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\RequiresSpatialDatabase;
use Tests\TestCase;

class EventCrudTest extends TestCase
{
    use RefreshDatabase;
    use RequiresSpatialDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipWithoutSpatialSupport();
    }

    public function test_guest_cannot_list_events(): void
    {
        $this->getJson('/api/events')->assertUnauthorized();
    }

    public function test_it_lists_events(): void
    {
        $user = User::factory()->create();
        Event::factory()->count(3)->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'coordinates' => ['latitude', 'longitude']]], 'meta', 'links']);
    }

    public function test_it_creates_an_event(): void
    {
        $user = User::factory()->create();
        $category = EventCategory::firstOrCreate(['slug' => 'other'], ['name' => 'Other']);
        $status = EventStatus::firstOrCreate(['slug' => 'draft'], ['name' => 'Draft']);
        Sanctum::actingAs($user);

        $payload = [
            'category_id' => $category->id,
            'status_id' => $status->id,
            'name' => 'Beach Cleanup',
            'city' => 'Lisbon',
            'country' => 'PT',
            'latitude' => 38.7223,
            'longitude' => -9.1393,
            'starts_at' => now()->addDay()->toIso8601String(),
            'is_outdoor' => true,
        ];

        $this->postJson('/api/events', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Beach Cleanup')
            ->assertJsonPath('data.coordinates.latitude', 38.7223);

        $this->assertDatabaseHas('events', [
            'name' => 'Beach Cleanup',
            'user_id' => $user->id,
        ]);
    }

    public function test_it_validates_on_create(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/events', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id', 'status_id', 'name', 'city', 'country', 'latitude', 'longitude', 'starts_at']);
    }

    public function test_it_shows_an_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $event->id);
    }

    public function test_it_updates_an_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->putJson("/api/events/{$event->id}", ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->assertDatabaseHas('events', ['id' => $event->id, 'name' => 'Renamed']);
    }

    public function test_it_deletes_an_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/events/{$event->id}")->assertNoContent();

        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_it_filters_by_city_and_outdoor(): void
    {
        $user = User::factory()->create();
        Event::factory()->for($user)->create(['city' => 'Porto', 'is_outdoor' => true]);
        Event::factory()->for($user)->create(['city' => 'Lisbon', 'is_outdoor' => false]);
        Sanctum::actingAs($user);

        $this->getJson('/api/events?city=Porto&is_outdoor=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.city', 'Porto');
    }

    public function test_it_sorts_and_paginates(): void
    {
        $user = User::factory()->create();
        Event::factory()->for($user)->create(['name' => 'Alpha', 'attendees' => 10]);
        Event::factory()->for($user)->create(['name' => 'Bravo', 'attendees' => 99]);
        Sanctum::actingAs($user);

        $this->getJson('/api/events?sort=attendees&direction=desc&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Bravo')
            ->assertJsonPath('meta.per_page', 1);
    }

    public function test_it_returns_404_for_missing_event(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/events/999999')->assertNotFound();
    }
}
