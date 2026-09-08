<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = AppNotification::factory()->create(['user_id' => $user->id, 'is_read' => false]);

        $this->actingAs($user)->patch(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertDatabaseHas('app_notifications', ['id' => $notification->id, 'is_read' => true]);
    }

    public function test_user_cannot_mark_others_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $notification = AppNotification::factory()->create(['user_id' => $owner->id, 'is_read' => false]);

        $this->actingAs($intruder)->patch(route('notifications.read', $notification))
            ->assertForbidden();
    }
}
