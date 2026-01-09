<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Mail::raw(
    'ShoppyCart test email sent at ' . now()->toDateTimeString(),
    function (Illuminate\Mail\Message $message) {
        $message->to(env('MAIL_ADMIN_ADDRESS'))->subject('ShoppyCart test email');
    }
);

echo "Test mail dispatched\n";
