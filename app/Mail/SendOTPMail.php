<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\OtpCode;

class SendOTPMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $email;
    protected $secret;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $secret)
    {
        $this->email = $email;
        $this->secret = $secret;
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
        $otp = OtpCode::where('email', $this->email)->where('secret', $this->secret)->where('status', 0)->first();
        if($otp) {
            $name = User::where('email', $this->email)->value('name');
            $code = $otp->code;
            return $this->from($from_address, $from_name)
                ->subject('Kode OTP - ToekangKu')
                ->view('email.send-otp')
                ->with(
                    [
                        'name' => $name,
                        'code' => $code
                    ]
                );
        }
    }
}
