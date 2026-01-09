<?php

namespace App\Jobs;

use App\Mail\DailySalesReportMail;
use App\Models\Sale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReport implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();

        $sales = Sale::with(['product', 'user'])
            ->whereBetween('sold_at', [$start, $end])
            ->get();

        $adminAddress = config('mail.admin_address');

        if ($adminAddress) {
            Mail::to($adminAddress)->send(new DailySalesReportMail($sales, $start));
        }
    }
}
