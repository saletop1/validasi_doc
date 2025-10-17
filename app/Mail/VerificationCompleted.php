<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public $doData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $doData)
    {
        $this->doData = $doData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Verifikasi DO Selesai: ' . $this->doData['do_number'])
                    ->view('emails.verification-completed');
    }
}
