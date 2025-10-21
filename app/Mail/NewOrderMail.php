<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Sale;

class NewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;

    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    public function build()
    {
        return $this->subject("New Order #{$this->sale->id}")
                    ->markdown('emails.orders.new')
                    ->with([
                        'sale' => $this->sale,
                    ]);
    }
}
