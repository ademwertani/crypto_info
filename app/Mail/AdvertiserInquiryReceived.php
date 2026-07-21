<?php

namespace App\Mail;

use App\Models\AdvertiserLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdvertiserInquiryReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public AdvertiserLead $lead)
    {
    }

    public function build(): self
    {
        return $this
            ->subject("New advertiser inquiry from {$this->lead->name}")
            ->view('emails.advertiser-inquiry-received');
    }
}
