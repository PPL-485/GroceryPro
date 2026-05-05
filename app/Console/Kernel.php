<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule automatic backup
        if (config('backup.schedule.enabled')) {
            $backup = $schedule->command('backup:run');
            
            $frequency = config('backup.schedule.frequency', 'daily');
            $time = config('backup.schedule.time', '02:00');
            
            // Set frequency
            if ($frequency === 'weekly') {
                $day = config('backup.schedule.day', 'Monday');
                $backup->weeklyOn($this->getDayOfWeek($day), $time);
            } elseif ($frequency === 'monthly') {
                $date = config('backup.schedule.date', 1);
                $backup->monthlyOn($date, $time);
            } else {
                // Default to daily
                $backup->dailyAt($time);
            }
            
            $backup->onSuccess(function () {
                \Illuminate\Support\Facades\Log::channel('backup')->info('Backup scheduled task completed successfully');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::channel('backup')->error('Backup scheduled task failed');
            });
        }
    }

    /**
     * Get day of week number
     */
    private function getDayOfWeek(string $day): int
    {
        $days = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];
        
        return $days[ucfirst(strtolower($day))] ?? 1;
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
