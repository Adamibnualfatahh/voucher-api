<?php

namespace App\Console\Commands;

use App\Jobs\GenerateVoucherJob;
use Illuminate\Console\Command;

class GenerateVoucher extends Command
{
    protected $signature   = 'voucher:generate {total=1000} {--batch=5000}';
    protected $description = 'Generate vouchers using queue jobs';

    public function handle(): void
    {
        $total     = (int) $this->argument('total');
        $batchSize = (int) $this->option('batch');

        $this->info("Dispatching {$total} vouchers into jobs...");
        $start = microtime(true);

        $jobs = ceil($total / $batchSize);

        for ($i = 0; $i < $jobs; $i++) {
            $size = min($batchSize, $total - ($i * $batchSize));
            GenerateVoucherJob::dispatch($size);
            $this->line('Dispatched batch '.($i + 1)." with {$size} vouchers");
        }

        $dispatchTime = round(microtime(true) - $start, 2);
        $this->info("Selesai dispatching {$total} vouchers in {$dispatchTime} detik.");
    }
}
