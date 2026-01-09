<?php

use App\Jobs\SendDailySalesReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Models\Sale;
use App\Mail\AdminDailySummaryMail;
use App\Mail\LowStockMail;
use App\Mail\DailySalesReportMail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(SendDailySalesReport::class)->dailyAt('18:00');
Schedule::command('lowstock:scan-now')->everyMinute();

// Quick console trigger to send Test, Low Stock, and Daily Sales Report to admin
Artisan::command('mail:test-admin', function () {
    $output = $this;

    $recipient = config('mail.admin_address');
    if (! is_string($recipient)) {
        $recipient = '';
    }
    $recipient = trim($recipient);
    if ($recipient === '' || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $output->error('Admin email not configured. Set MAIL_ADMIN_ADDRESS to a valid email in .env.');
        return 1;
    }

    $mailer = config('mail.default');
    if ($mailer === 'log') {
        $output->warn('Current mailer is "log". Emails will be written to storage/logs/laravel.log (development mode).');
    }
    $mailConfig = config('mail.mailers.' . $mailer);
    if (empty($mailer) || empty($mailConfig)) {
        $output->error('Mailer not configured. Ensure MAIL_MAILER is set and matches a configured mailer in config/mail.php.');
        return 1;
    }
    if ($mailer === 'smtp') {
        $host = $mailConfig['host'] ?? env('MAIL_HOST');
        $port = $mailConfig['port'] ?? env('MAIL_PORT');
        $username = env('MAIL_USERNAME');
        $password = env('MAIL_PASSWORD');
        if (empty($host) || empty($port) || empty($username) || empty($password)) {
            $output->warn('SMTP settings incomplete. Using current settings; failover may log instead of sending.');
        }
    }
    $from = config('mail.from.address') ?? env('MAIL_FROM_ADDRESS');
    if (! $from || ! filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $output->error('MAIL_FROM_ADDRESS is missing or invalid in .env.');
        return 1;
    }

    // Combined: daily sales + low stock products in one email
    $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->orderBy('name')->get();
    $lowStockCount = $lowStockProducts->count();

    // Daily sales report for today
    $start = Carbon::today()->startOfDay();
    $end = Carbon::today()->endOfDay();
    $sales = Sale::with(['product', 'user'])
        ->whereBetween('sold_at', [$start, $end])
        ->get();

    try {
        Mail::to($recipient)->send(new AdminDailySummaryMail($sales, $start, $lowStockProducts));
        $output->info('Sent: AdminDailySummaryMail');
    } catch (\Throwable $e) {
        $output->error('Failed (admin daily summary): ' . $e->getMessage());
        return 1;
    }

    $output->info('Done. Recipient: ' . $recipient . ' (sales records: ' . $sales->count() . ', low stock products: ' . $lowStockCount . ')');
    return 0;
})->purpose('Send admin test emails (test, low stock, daily report)');

// Download product images to public/assets/product/prod_images and update image paths
Artisan::command('products:download-images', function () {
    $output = $this;
    $baseDir = public_path('assets/product/prod_images');

    if (! is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
        $output->info("Created directory: {$baseDir}");
    }

    // Ensure placeholder exists
    $placeholderPath = $baseDir . DIRECTORY_SEPARATOR . 'placeholder.svg';
    if (! file_exists($placeholderPath)) {
        file_put_contents($placeholderPath, '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400"><rect width="600" height="400" fill="#e5e7eb"/><g fill="#6b7280" font-family="Arial, Helvetica, sans-serif" text-anchor="middle"><text x="300" y="190" font-size="28">Image Unavailable</text><text x="300" y="225" font-size="16">Local placeholder</text></g></svg>');
        $output->info('Created placeholder image.');
    }

    $relativePlaceholder = 'assets/product/prod_images/placeholder.svg';

    $count = 0;
    // Curated Wikimedia Commons image URLs for stable downloads
    $map = [
        'Apples' => 'https://upload.wikimedia.org/wikipedia/commons/1/15/Red_Apple.jpg',
        'Bananas' => 'https://upload.wikimedia.org/wikipedia/commons/8/8a/Banana-Single.jpg',
        'Oranges' => 'https://upload.wikimedia.org/wikipedia/commons/c/c4/Orange-Fruit-Pieces.jpg',
        'Milk' => 'https://upload.wikimedia.org/wikipedia/commons/1/17/Milk_glass.jpg',
        'Eggs' => 'https://upload.wikimedia.org/wikipedia/commons/0/0a/Chicken_eggs.jpg',
        'Cheddar Cheese' => 'https://upload.wikimedia.org/wikipedia/commons/0/0b/Cheddar_cheese.jpg',
        'Butter' => 'https://upload.wikimedia.org/wikipedia/commons/1/1c/Butter.jpg',
        'Greek Yogurt' => 'https://upload.wikimedia.org/wikipedia/commons/6/6b/Yoghurt.jpg',
        'Bread Loaf' => 'https://upload.wikimedia.org/wikipedia/commons/0/0f/Loaf-Of-Bread.jpg',
        'Pasta' => 'https://upload.wikimedia.org/wikipedia/commons/5/55/Penne_pasta.jpg',
        'White Rice' => 'https://upload.wikimedia.org/wikipedia/commons/6/6f/Rice.jpg',
        'Tomatoes' => 'https://upload.wikimedia.org/wikipedia/commons/8/89/Tomato_je.jpg',
        'Potatoes' => 'https://upload.wikimedia.org/wikipedia/commons/a/ab/Patates.jpg',
        'Onions' => 'https://upload.wikimedia.org/wikipedia/commons/7/7d/Onions.jpg',
        'Carrots' => 'https://upload.wikimedia.org/wikipedia/commons/7/7f/Carrots.jpg',
        'Broccoli' => 'https://upload.wikimedia.org/wikipedia/commons/0/03/Broccoli_and_cross_section.jpg',
        'Lettuce' => 'https://upload.wikimedia.org/wikipedia/commons/8/80/Lettuce.jpg',
        'Chicken Breast' => 'https://upload.wikimedia.org/wikipedia/commons/7/71/Raw_chicken_breast.jpg',
        'Ground Beef' => 'https://upload.wikimedia.org/wikipedia/commons/f/f9/Minced_meat.jpg',
        'Strawberries' => 'https://upload.wikimedia.org/wikipedia/commons/2/29/PerfectStrawberry.jpg',
    ];

    Product::all()->each(function (Product $p) use ($baseDir, $output, &$count, $map) {
        $slug = Str::slug($p->name, '-');
        $filePath = $baseDir . DIRECTORY_SEPARATOR . $slug . '.jpg';
        $relative = 'assets/product/prod_images/' . $slug . '.jpg';

        if (file_exists($filePath)) {
            // Update DB to ensure it points to local file
            if ($p->image_url !== $relative) {
                $p->image_url = $relative;
                $p->save();
            }
            return;
        }

        $query = strtolower(str_replace([' ', '/'], [',', ','], $p->name)) . ',food';
        $candidates = [
            $map[$p->name] ?? null,
            'https://loremflickr.com/600/400/' . urlencode($query),
            'https://source.unsplash.com/600x400/?' . $query,
        ];

        $downloaded = false;
        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }
            try {
                $response = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
                        'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    ])
                    ->timeout(25)
                    ->retry(2, 500)
                    ->get($candidate);
                if ($response->successful()) {
                    file_put_contents($filePath, $response->body());
                    $p->image_url = $relative;
                    $p->save();
                    $count++;
                    $output->info("Downloaded: {$p->name} -> {$filePath}");
                    $downloaded = true;
                    break;
                } else {
                    $output->warn("Failed: {$p->name} from " . $candidate . " (HTTP " . $response->status() . ")");
                }
            } catch (\Throwable $e) {
                $output->warn("Error for {$p->name} from " . $candidate . ": " . $e->getMessage());
            }
        }

        // If not downloaded, assign placeholder
        if (! $downloaded) {
            $p->image_url = $relativePlaceholder;
            $p->save();
            $output->info("Assigned placeholder: {$p->name} -> {$relativePlaceholder}");
        }
    });

    $output->info("Images processed. New downloads: {$count}");
})->purpose('Download product images and set local image paths');

// Normalize product prices to realistic values
Artisan::command('products:normalize-prices', function () {
    $output = $this;

    $priceMap = [
        'Apples' => 2.99,
        'Bananas' => 1.29,
        'Oranges' => 3.49,
        'Milk' => 2.49,
        'Eggs' => 3.99,
        'Cheddar Cheese' => 4.79,
        'Butter' => 3.49,
        'Greek Yogurt' => 5.99,
        'Bread Loaf' => 2.99,
        'Pasta' => 1.99,
        'White Rice' => 3.49,
        'Tomatoes' => 2.49,
        'Potatoes' => 3.99,
        'Onions' => 1.99,
        'Carrots' => 1.49,
        'Broccoli' => 2.29,
        'Lettuce' => 1.99,
        'Chicken Breast' => 7.99,
        'Ground Beef' => 6.49,
        'Strawberries' => 4.99,
    ];

    $updated = 0;
    Product::all()->each(function (Product $p) use ($priceMap, $output, &$updated) {
        if (array_key_exists($p->name, $priceMap)) {
            $new = $priceMap[$p->name];
            if ((float) $p->price !== (float) $new) {
                $p->price = $new;
                $p->save();
                $updated++;
                $output->info("Updated price: {$p->name} -> $" . number_format($new, 2));
            }
        }
    });

    $output->info("Price normalization complete. Updated: {$updated}");
})->purpose('Set realistic product prices by name');

// Manual trigger to scan and notify low-stock products immediately
Artisan::command('lowstock:scan-now', function () {
    $output = $this;
    $count = 0;
    $products = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
        ->where(function($q){ $q->whereNull('low_stock_notified')->orWhere('low_stock_notified', false); })
        ->orderBy('name')
        ->get();

    foreach ($products as $p) {
        \App\Jobs\NotifyLowStock::dispatch($p->id);
        $count++;
    }

    if ($count === 0) {
        $output->info('No new low-stock products found.');
    } else {
        $output->info('Dispatched low-stock notifications for ' . $count . ' product(s).');
    }
})->purpose('Scan and dispatch low-stock notifications');
