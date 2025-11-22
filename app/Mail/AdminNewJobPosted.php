<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewJobPosted extends Mailable
{


    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
   public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Admin New Job Posted',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.new-job-posted',
        );
    }

    public function build()
    {
      
           return $this->subject('New Job Posted - Action Required')
        ->view('emails.admin.new-job-posted')
        ->with([
            'employer' => $this->data['employer'],
            'jobPosting' => $this->data['jobPosting'],
            'company' => $this->data['company'],
            'isNewCompany' => $this->data['isNewCompany'],
            'joiningFeeText' => $this->data['joiningFeeText'],
        ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
