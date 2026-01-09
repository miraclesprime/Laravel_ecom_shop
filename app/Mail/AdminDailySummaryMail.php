<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminDailySummaryMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Collection $sales,
        public Carbon $date,
        public Collection $lowStockProducts
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Admin Summary - ' . $this->date->toDateString(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin_daily_summary',
            with: [
                'sales' => $this->sales,
                'date' => $this->date,
                'lowStockProducts' => $this->lowStockProducts,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
