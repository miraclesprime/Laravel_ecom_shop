<?php

namespace App\Livewire;

use App\Mail\AdminDailySummaryMail;
use App\Mail\DailySalesReportMail;
use App\Mail\LowStockMail;
use App\Models\Product;
use App\Models\Sale;
use App\Services\CartService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;


class ProductsIndex extends Component
{
    public ?string $statusMessage = null;
    public string $statusType = 'success';
    
    public function mount(): void
    {
        // Check if any low stock alerts were just sent and show them
        $products = Product::all();
        foreach ($products as $product) {
            $alertKey = "low_stock_alert_{$product->id}";
            if (Cache::has($alertKey)) {
                $productName = Cache::get($alertKey);
                $this->dispatch('notify', message: "✉️ Low stock alert email sent for {$productName}", type: 'info');
                // Remove the flag so we don't show it again
                Cache::forget($alertKey);
            }
        }
    }
    
    #[On('low-stock-email-sent')]
    public function onLowStockEmailSent(string $productName): void
    {
        $this->dispatch('notify', message: "✉️ Low stock alert email sent for {$productName}", type: 'info');
    }

    #[On('cart-updated')]
    public function render()
    {
        $products = Product::orderBy('name')->get();

        return view('livewire.products-index', [
            'products' => $products,
        ]);
    }

    public function addToCart(int $productId): void
    {
        $userId = auth()->id();

        // Prevent guest users from attempting cart operations
        if (! $userId) {
            $this->dispatch('notify', message: 'Please sign in to add items to your cart.', type: 'error');
            return;
        }

        try {
            app(CartService::class)->addToCart($userId, $productId, 1);
        } catch (DomainException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Added to cart!', type: 'success');
    }

    /**
     * Trigger test emails (low stock + daily sales report)
     */
    public function testEmails(): void
    {
        $recipient = (string) config('mail.admin_address');

        // Send low stock email for every low-stock product
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->get();
        $lowStockCount = $lowStockProducts->count();

        foreach ($lowStockProducts as $product) {
            Mail::to($recipient)->send(new LowStockMail($product));
        }

        // If no low stock products, at least send one sample
        if ($lowStockCount === 0) {
            $fallback = Product::first();
            if ($fallback) {
                Mail::to($recipient)->send(new LowStockMail($fallback));
            }
        }

        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();

        // Fetch all sales made today (all users)
        $sales = Sale::with(['product', 'user'])
            ->whereBetween('sold_at', [$start, $end])
            ->get();

        Mail::to($recipient)->send(new DailySalesReportMail($sales, $start));

        $this->dispatch('notify', message: 'Test emails sent to ' . $recipient . ' (low stock: ' . max($lowStockCount, 1) . ' email(s), sales: ' . $sales->count() . ' records)', type: 'success');
    }

    /**
     * Combined action: send a simple email + low stock + daily sales report
     * to the admin user's inbox.
     */
    public function sendEmails(): void
    {
        // Resolve admin recipient from config and validate
        $recipient = (string) config('mail.admin_address', '');
        $recipient = trim($recipient);
        if ($recipient === '' || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->statusType = 'error';
            $this->statusMessage = 'Admin email not configured. Please set mail.admin_address / MAIL_ADMIN_ADDRESS to a valid email.';
            return;
        }

        // Preflight mailer configuration validation
        $mailer = config('mail.default');
        if ($mailer === 'log') {
            $this->statusType = 'error';
            $this->statusMessage = 'Current mailer is "log". Emails are written to storage/logs/laravel.log and will not be delivered. Set MAIL_MAILER=smtp with real credentials to send to inbox.';
            return;
        }
        $mailConfig = config('mail.mailers.' . $mailer);
        if (empty($mailer) || empty($mailConfig)) {
            $this->statusType = 'error';
            $this->statusMessage = 'Mailer not configured. Ensure MAIL_MAILER is set and matches a configured mailer in config/mail.php.';
            return;
        }
        if ($mailer === 'smtp') {
            $host = $mailConfig['host'] ?? env('MAIL_HOST');
            $port = $mailConfig['port'] ?? env('MAIL_PORT');
            $username = env('MAIL_USERNAME');
            $password = env('MAIL_PASSWORD');
            if (empty($host) || empty($port) || empty($username) || empty($password)) {
                $this->statusType = 'error';
                $this->statusMessage = 'SMTP settings incomplete. Please set MAIL_HOST, MAIL_PORT, MAIL_USERNAME and MAIL_PASSWORD in .env.';
                return;
            }
        }
        $from = config('mail.from.address') ?? env('MAIL_FROM_ADDRESS');
        if (! $from || ! filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->statusType = 'error';
            $this->statusMessage = 'MAIL_FROM_ADDRESS is missing or invalid in .env.';
            return;
        }

        // Low stock products
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->orderBy('name')->get();
        $lowStockCount = $lowStockProducts->count();

        // Daily sales report for today
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();
        $sales = Sale::with(['product', 'user'])
            ->whereBetween('sold_at', [$start, $end])
            ->get();

        // Send single combined email: sales + low stock
        try {
            Mail::to($recipient)->send(new AdminDailySummaryMail($sales, $start, $lowStockProducts));
        } catch (\Throwable $e) {
            $this->statusType = 'error';
            $this->statusMessage = 'Email could not be sent: ' . $e->getMessage();
            return;
        }

        $this->statusType = 'success';
        $this->statusMessage = 'Email sent to ' . $recipient . ' (sales: ' . $sales->count() . ' records, low stock: ' . $lowStockCount . ' products)';
    }
}
