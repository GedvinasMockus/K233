<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendReportInfo extends Mailable
{
    use Queueable, SerializesModels;

    public $time;
    public $description;
    public $name;
    public $address;
    public $image;
    public function __construct($details)
    {
        $this->time = $details['time'];
        $this->description = $details['description'];
        $this->name = $details['name'];
        $this->address = $details['address'];
        $this->image = $details['image'];
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pažeidėjo ' . $this->name . ' aikštelėje pranešimas',
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'Emails.ReportInfo',
        );
    }
    public function attachments(): array
    {
        return [];
    }
}
