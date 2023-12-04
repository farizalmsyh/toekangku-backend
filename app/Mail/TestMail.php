<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from_address = env('MAIL_FROM_ADDRESS', 'no-reply@gmail.com');
        $from_name = env('MAIL_FROM_NAME', 'Laravel');
        return $this->from($from_address, $from_name)
            ->subject('Testing Email - ToekangKu')
            ->view('email.testing');
    }
}
