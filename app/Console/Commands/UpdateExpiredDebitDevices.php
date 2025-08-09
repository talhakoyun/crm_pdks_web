<?php

namespace App\Console\Commands;

use App\Models\UserDebitDevice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateExpiredDebitDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debit:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update expired debit device assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');

        // Süresi dolmuş aktif zimmetleri bul
        $expiredAssignments = UserDebitDevice::where('status', 'active')
            ->where('end_date', '<', $today)
            ->get();

        $count = $expiredAssignments->count();

        if ($count > 0) {
            foreach ($expiredAssignments as $assignment) {
                $assignment->status = 'expired';
                $assignment->save();

                $this->info("Zimmet #" . $assignment->id . " süresi doldu olarak işaretlendi.");
            }

            $this->info("Toplam {$count} zimmet süresi doldu olarak işaretlendi.");
        } else {
            $this->info("Süresi dolmuş zimmet bulunamadı.");
        }

        return 0;
    }
}
