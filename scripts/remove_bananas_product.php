<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$deleted = Product::whereIn('name', ['Bananas', 'Banana'])->delete();

echo "Removed $deleted product(s) named Bananas/Banana\n";