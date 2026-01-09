<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DailySalesReportMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Collection $sales, public Carbon $date)
    {
        // Sales data and the report date are injected by the caller.
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Sales Report - '.$this->date->toDateString(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily_sales_report',
            with: [
                'sales' => $this->sales,
                'date' => $this->date,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
