<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobPostingMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
  public $jobPosting;

    /**
     * Create a new message instance.
     *
     * @param JobPosting $jobPosting
     */
    public function __construct(JobPosting $jobPosting)
    {
        $this->jobPosting = $jobPosting;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Job Posting Awaiting Activation')
                    ->view('emails.job_posting')
                    ->with([
                        'jobTitle' => $this->jobPosting->job_title,
                        'employerId' => $this->jobPosting->employer_id,
                        'jobType' => $this->jobPosting->job_type,
                        'location' => $this->jobPosting->location
                    ]);
    }
}
