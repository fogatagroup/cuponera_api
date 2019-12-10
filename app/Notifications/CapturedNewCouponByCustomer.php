<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Customer;

class CapturedNewCouponByCustomer extends Notification
{
    use Queueable;

    protected $offerts;
    protected $offert_code;

    /**
     * Create a new notification instance.
     * @param Customer $customer
     * @return void
     */
    public function __construct(array $offerts, string $offert_code)
    {
        $this->offerts = $offerts;
        $this->offert_code = $offert_code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject('Cupon Capturado '.$this->offert_code.' - La Strega')
        ->view(
            'emails.couponcapture', [
                'offerts' => $this->offerts,
                ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
