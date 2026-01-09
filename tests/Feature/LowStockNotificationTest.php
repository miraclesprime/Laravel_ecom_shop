<?php

namespace Tests\Feature;

use App\Jobs\NotifyLowStock;
use App\Mail\LowStockSummaryMail;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_job_is_dispatched_when_stock_falls_below_threshold(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 5,
        ]);

        // Simulate stock decrease
        $product->decrement('stock_quantity', 1);

        NotifyLowStock::dispatch($product->id);

        Queue::assertPushed(NotifyLowStock::class);
    }

    public function test_low_stock_email_is_sent(): void
    {
        Mail::fake();

        config(['mail.admin_address' => 'admin@example.com']);

        $product = Product::factory()->create([
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);

        $job = new NotifyLowStock($product->id);
        $job->handle();

        Mail::assertSent(LowStockSummaryMail::class, function ($mail) {
            return $mail->hasTo('admin@example.com');
        });
    }

    public function test_low_stock_email_not_sent_without_admin_email(): void
    {
        Mail::fake();

        config(['mail.admin_address' => null]);

        $product = Product::factory()->create([
            'stock_quantity' => 2,
            'low_stock_threshold' => 5,
        ]);

        $job = new NotifyLowStock($product->id);
        $job->handle();

        Mail::assertNothingSent();
    }
}
