<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use App\Models\Product;

class SupplyLowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $supply;

    public function __construct(Product $supply)
    {
        $this->supply = $supply;
    }

    public function via($notifiable)
    {
        $channels = ['mail'];
        if (env('SLACK_WEBHOOK_URL')) {
            $channels[] = 'slack';
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Supply Low: ' . $this->supply->name)
                    ->line('Supply "' . $this->supply->name . '" is low.')
                    ->line('Current stock: ' . $this->supply->stock)
                    ->action('Manage Supply', url(route('products.supplies.edit', $this->supply->id)))
                    ->line('Please restock as needed.');
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content('Supply low: ' . $this->supply->name . ' (stock: ' . $this->supply->stock . ')')
            ->attachment(function ($attachment) {
                $attachment->title('Manage Supply', url(route('products.supplies.edit', $this->supply->id)));
            });
    }
}
