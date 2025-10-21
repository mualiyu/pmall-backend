<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPurchaseNotification extends Notification
{
    use Queueable;
     protected $sale;

     protected $email;
    protected $phone;
    /**
     * Create a new notification instance.
     */
    public function __construct($sale, $email, $phone)
    {
        $this->sale = $sale;
         $this->email = $email;
        $this->phone = $phone;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('New Purchase Alert!')
        ->view('emails.vendor_purchase', [
            'sale' => $this->sale,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
             'message' => 'A new sale was made from your store. Sale ID: ' . $this->sale->id,
            'total_amount' => $this->sale->total_amount,
            'sale_id' => $this->sale->id,
        ];
    }
}
