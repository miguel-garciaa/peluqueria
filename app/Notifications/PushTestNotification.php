<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PushTestNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $isAdmin = method_exists($notifiable, 'isPanelAdmin') && $notifiable->isPanelAdmin();

        return (new WebPushMessage)
            ->title('Notificaciones activadas')
            ->body($isAdmin
                ? 'Todo listo. Las nuevas reservas aparecerán en este dispositivo.'
                : 'Todo listo. Te avisaremos de los cambios de tus citas.')
            ->icon('/icons/icon-192.png')
            ->badge('/icons/icon-192.png')
            ->lang('es')
            ->tag('push-test')
            ->renotify()
            ->vibrate([180, 80, 180])
            ->data(['url' => $isAdmin ? '/admin' : '/mis-citas'])
            ->options([
                'TTL' => 300,
                'urgency' => 'high',
                'topic' => 'push-test',
            ]);
    }
}
