<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCompanyRegistered extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
  public function __construct($employer)
    {
        $this->employer = $employer;
    }

    public function build()
    {
        return $this->subject('New Company Registered')
                    ->view('emails.new_company_registered')
                    ->with([
                        'companyName' => $this->employer->company_name,
                        'contactPerson' => $this->employer->contact_person,
                        'contactEmail' => $this->employer->contact_email,
                        'location' => $this->employer->company_location,
                        'phone' => $this->employer->contact_phone,
                    ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Company Registered',
        );
    }

  
    public function attachments(): array
    {
        return [];
    }
}
