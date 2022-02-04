<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordUpdated extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    public function __construct($user){
        $this->user = $user;
    }

    public function build(){
        $subject = __('app.password-updated');

        $data = [
            'subject' => $subject,
            'user' => $this->user,
        ];

        $from = env('MAIL_FROM_ADDRESS');
        $fromName = env('MAIL_FROM_ADDRESS_NAME');

        return $this->from($from, $fromName)
            ->subject($subject)
            ->view('emails.password-updated')
            ->with($data);
    }
}
