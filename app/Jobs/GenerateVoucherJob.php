<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateVoucherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function handle(): void
    {
        $batch = [];
        for ($i = 0; $i < $this->count; $i++) {
            $batch[] = [
                'code' => strtoupper(Str::random(12)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('vouchers')->insert($batch);
    }
}
