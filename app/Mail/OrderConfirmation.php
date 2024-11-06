<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class OrderConfirmation extends Mailable
{
    public $orderData;

    public function __construct($orderData)
    {
        $this->orderData = $orderData;
    }

    public function build()
    {
        return $this->view('emails.order_confirmation')
                    ->with(['orderData' => $this->orderData])
                    ->subject('BookStore - Xác nhận đơn hàng của bạn');
    }
}
