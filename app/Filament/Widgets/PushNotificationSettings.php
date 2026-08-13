<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PushNotificationSettings extends Widget
{
    protected string $view = 'filament.widgets.push-notification-settings';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    /** @return array<string, string> */
    protected function getViewData(): array
    {
        return [
            'publicKey' => (string) config('webpush.vapid.public_key'),
            'subscriptionEndpoint' => route('push-subscriptions.store', absolute: false),
            'csrfToken' => csrf_token(),
        ];
    }
}
