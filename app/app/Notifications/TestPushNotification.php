<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class TestPushNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['webpush'];  // WebPushチャンネルを指定
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('新しいメッセージが届きました')
            ->body($this->messageContent)
            ->action('アプリを開く', 'view_message');
    }
}