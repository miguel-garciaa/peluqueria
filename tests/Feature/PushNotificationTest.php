<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AdminAppointmentDatabaseNotification;
use App\Notifications\AppointmentPushNotification;
use App\Services\AppointmentPushNotifications;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('webpush.vapid.public_key', str_repeat('A', 87));
        config()->set('webpush.vapid.private_key', str_repeat('B', 43));
    }

    public function test_an_authenticated_user_can_enable_and_disable_push_notifications(): void
    {
        $user = User::factory()->create();
        $payload = $this->subscriptionPayload();

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertOk()
            ->assertJsonPath('message', 'Notificaciones activadas.');

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_type' => User::class,
            'subscribable_id' => $user->id,
            'endpoint' => $payload['endpoint'],
            'content_encoding' => 'aes128gcm',
        ]);

        $this->actingAs($user)
            ->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $payload['endpoint']])
            ->assertOk();

        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_push_subscription_endpoints_are_authenticated_and_restricted_to_known_providers(): void
    {
        $payload = $this->subscriptionPayload();
        $this->postJson(route('push-subscriptions.store'), $payload)->assertUnauthorized();

        $payload['endpoint'] = 'https://internal.example.test/collect';
        $this->actingAs(User::factory()->create())
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('endpoint');
    }

    public function test_booking_pushes_are_sent_to_the_customer_and_subscribed_admins(): void
    {
        Notification::fake();
        [$appointment, $customer] = $this->appointmentAt(now()->addDays(3));
        $admin = User::factory()->create(['is_admin' => true]);
        $customer->updatePushSubscription(...$this->subscriptionArguments('customer'));
        $admin->updatePushSubscription(...$this->subscriptionArguments('admin'));

        app(AppointmentPushNotifications::class)->booked($appointment);

        Notification::assertSentTo($customer, AppointmentPushNotification::class,
            fn (AppointmentPushNotification $notification): bool => $notification->event === AppointmentPushNotification::CONFIRMED);
        Notification::assertSentTo($admin, AppointmentPushNotification::class,
            fn (AppointmentPushNotification $notification): bool => $notification->event === AppointmentPushNotification::ADMIN_CREATED);
        Notification::assertSentTo($admin, AdminAppointmentDatabaseNotification::class,
            fn (AdminAppointmentDatabaseNotification $notification): bool => $notification->event === AppointmentPushNotification::ADMIN_CREATED);
    }

    public function test_the_admin_panel_receives_a_persistent_notification_even_without_vapid(): void
    {
        config()->set('webpush.vapid.public_key');
        config()->set('webpush.vapid.private_key');
        [$appointment] = $this->appointmentAt(now()->addDays(3));
        $admin = User::factory()->create(['is_admin' => true]);

        app(AppointmentPushNotifications::class)->booked($appointment);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'type' => AdminAppointmentDatabaseNotification::class,
        ]);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_the_admin_panel_receives_a_persistent_cancellation_notification(): void
    {
        config()->set('webpush.vapid.public_key');
        config()->set('webpush.vapid.private_key');
        [$appointment] = $this->appointmentAt(now()->addDays(3));
        $admin = User::factory()->create(['is_admin' => true]);

        app(AppointmentPushNotifications::class)->cancelled($appointment);

        $notification = $admin->fresh()->notifications()->sole();

        $this->assertSame('Cita anulada', $notification->data['title']);
        $this->assertSame('danger', $notification->data['status']);
    }

    public function test_a_reminder_is_enqueued_only_once_during_the_24_hours_before_an_appointment(): void
    {
        Notification::fake();
        CarbonImmutable::setTestNow('2026-08-13 10:00:00');
        [$appointment, $customer] = $this->appointmentAt(now()->addHours(23));
        $customer->updatePushSubscription(...$this->subscriptionArguments('reminder'));

        $this->artisan('appointments:send-push-reminders')->assertSuccessful();
        $this->artisan('appointments:send-push-reminders')->assertSuccessful();

        Notification::assertSentToTimes($customer, AppointmentPushNotification::class, 1);
        Notification::assertSentTo($customer, AppointmentPushNotification::class,
            fn (AppointmentPushNotification $notification): bool => $notification->event === AppointmentPushNotification::REMINDER);
        $this->assertNotNull($appointment->fresh()->push_reminder_sent_at);

        CarbonImmutable::setTestNow();
    }

    /** @return array{Appointment, User} */
    private function appointmentAt(mixed $startsAt): array
    {
        $customer = User::factory()->create();
        $service = Service::query()->create([
            'slug' => 'corte-test',
            'name' => 'Corte',
            'duration_minutes' => 45,
            'price_from' => 25,
            'is_active' => true,
        ]);
        $professional = Professional::query()->create([
            'slug' => 'ana-test',
            'name' => 'Ana',
            'is_active' => true,
        ]);

        $appointment = Appointment::query()->forceCreate([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'customer_name' => $customer->name,
            'customer_phone' => '+34 600 12 34 56',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(45),
            'status' => 'confirmed',
        ]);

        return [$appointment, $customer];
    }

    /** @return array{endpoint: string, keys: array{p256dh: string, auth: string}, contentEncoding: string} */
    private function subscriptionPayload(string $suffix = 'device'): array
    {
        return [
            'endpoint' => "https://fcm.googleapis.com/fcm/send/{$suffix}",
            'keys' => [
                'p256dh' => str_repeat('C', 87),
                'auth' => str_repeat('D', 22),
            ],
            'contentEncoding' => 'aes128gcm',
        ];
    }

    /** @return array{string, string, string, string} */
    private function subscriptionArguments(string $suffix): array
    {
        $payload = $this->subscriptionPayload($suffix);

        return [$payload['endpoint'], $payload['keys']['p256dh'], $payload['keys']['auth'], $payload['contentEncoding']];
    }
}
