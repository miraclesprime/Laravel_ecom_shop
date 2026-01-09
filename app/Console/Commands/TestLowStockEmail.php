<?php

namespace App\Console\Commands;

use App\Jobs\NotifyLowStock;
use App\Models\Product;
use Illuminate\Console\Command;

class TestLowStockEmail extends Command
{
    protected $signature = 'test:low-stock-email {product_id=1}';
    protected $description = 'Test low stock email notification for a product';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $product = Product::find($productId);

        if (!$product) {
            $this->error("Product {$productId} not found");
            return 1;
        }

        $this->info("Triggering low stock notification for: {$product->name}");
        $this->info("Admin email: " . env('MAIL_ADMIN_ADDRESS'));

        // Dispatch synchronously
        NotifyLowStock::dispatch($product->id)->onQueue(null);

        $this->info("Email dispatch completed! Check your email inbox.");
        return 0;
    }
}
