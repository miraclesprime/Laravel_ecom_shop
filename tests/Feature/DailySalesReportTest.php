<?php

namespace Tests\Feature;

use App\Jobs\SendDailySalesReport;
use App\Mail\DailySalesReportMail;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DailySalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_sales_report_is_sent(): void
    {
        Mail::fake();

        config(['mail.admin_address' => 'admin@example.com']);

        $user = User::factory()->create();
        $product = Product::factory()->create();

        Sale::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 100.00,
            'sold_at' => now(),
        ]);

        $job = new SendDailySalesReport();
        $job->handle();

        Mail::assertSent(DailySalesReportMail::class, function ($mail) {
            return $mail->hasTo('admin@example.com');
        });
    }

    public function test_daily_sales_report_only_includes_todays_sales(): void
    {
        Mail::fake();

        config(['mail.admin_address' => 'admin@example.com']);

        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Today's sale
        Sale::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'sold_at' => now(),
        ]);

        // Yesterday's sale (should not be included)
        Sale::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'sold_at' => now()->subDay(),
        ]);

        $job = new SendDailySalesReport();
        $job->handle();

        Mail::assertSent(DailySalesReportMail::class, function ($mail) {
            return count($mail->sales) === 1;
        });
    }

    public function test_daily_sales_report_not_sent_without_admin_email(): void
    {
        Mail::fake();

        config(['mail.admin_address' => null]);

        $job = new SendDailySalesReport();
        $job->handle();

        Mail::assertNothingSent();
    }
}
