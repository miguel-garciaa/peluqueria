<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentPushNotification;
use Illuminate\Support\Facades\Notification;
use Throwable;

class AppointmentPushNotifications
{
    public function booked(Appointment $appointment): void
    {
        if (! $this->configured()) {
            return;
        }

        $this->notifyCustomer($appointment, AppointmentPushNotification::CONFIRMED);
        $this->notifyAdmins($appointment, AppointmentPushNotification::ADMIN_CREATED);
    }

    public function customerUpdated(Appointment $appointment): void
    {
        $this->notifyCustomer($appointment, AppointmentPushNotification::UPDATED);
    }

    public function customerConfirmed(Appointment $appointment): void
    {
        $this->notifyCustomer($appointment, AppointmentPushNotification::CONFIRMED);
    }

    public function cancelled(Appointment $appointment): void
    {
        if (! $this->configured()) {
            return;
        }

        $this->notifyCustomer($appointment, AppointmentPushNotification::CANCELLED);
        $this->notifyAdmins($appointment, AppointmentPushNotification::ADMIN_CANCELLED);
    }

    public function reminder(Appointment $appointment): void
    {
        $this->notifyCustomer($appointment, AppointmentPushNotification::REMINDER);
    }

    public function configured(): bool
    {
        return filled(config('webpush.vapid.public_key')) && filled(config('webpush.vapid.private_key'));
    }

    private function notifyCustomer(Appointment $appointment, string $event): void
    {
        if (! $this->configured()) {
            return;
        }

        $appointment->loadMissing('user.pushSubscriptions');
        if ($appointment->user->pushSubscriptions->isEmpty()) {
            return;
        }

        $appointment->user->notify(new AppointmentPushNotification($appointment, $event));
    }

    private function notifyAdmins(Appointment $appointment, string $event): void
    {
        $admins = User::query()->admins()->whereHas('pushSubscriptions')->get();
        if ($admins->isEmpty()) {
            return;
        }

        foreach ($admins as $admin) {
            try {
                // New bookings must reach the owner's phone even if a queue worker is unavailable.
                Notification::sendNow($admin, new AppointmentPushNotification($appointment, $event));
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
