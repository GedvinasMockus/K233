<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReportAnswer extends Mailable
{
    use Queueable, SerializesModels;

    public $time;
    public $description;
    public $name;
    public $address;
    public $image;
    public $answer;
    public $admin;
    public $adminEmail;
    public function __construct($details)
    {
        $this->time = $details['time'];
        $this->description = $details['description'];
        $this->name = $details['name'];
        $this->address = $details['address'];
        $this->image = $details['image'];
        $this->answer = $details['answer'];
        $this->admin = $details['admin'];
        $this->adminEmail = $details['adminEmail'];
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Atsakymas į pažeidimą aikštelėje ' . $this->name . ' ' . $this->time . '!',
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'Emails.ReportAnswer',
        );
    }
    public function attachments(): array
    {
        return [];
    }
}
