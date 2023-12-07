<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user_id;
    protected $title;
    protected $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_id, $title, $body)
    {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->body = $body;
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
        $user = User::find($this->user_id);
        if($user) {
            return $this->from($from_address, $from_name)
                ->subject('Notifikasi - '.$this->title.' - ToekangKu')
                ->view('email.notification')
                ->with(
                    [
                        'name' => $user->name,
                        'title' => $this->title,
                        'body' => $this->body,
                    ]
                );
        }
    }
}
