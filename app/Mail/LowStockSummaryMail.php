<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockSummaryMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Product $trigger,
        public Collection $lowStockProducts
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Low Stock Update: ' . $this->trigger->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.low_stock_summary',
            with: [
                'trigger' => $this->trigger,
                'lowStockProducts' => $this->lowStockProducts,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
