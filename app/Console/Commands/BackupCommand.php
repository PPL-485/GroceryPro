<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run {--cloud : Upload to cloud storage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a complete backup of database and storage files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting backup process...');
        $this->newLine();

        $backupService = new BackupService();
        $result = $backupService->backup();

        $this->newLine();

        if ($result['success']) {
            $this->info('✓ ' . $result['message']);
            $this->line("📦 Backup name: {$result['fileName']}");
            
            return Command::SUCCESS;
        } else {
            $this->error('✗ ' . $result['message']);
            
            return Command::FAILURE;
        }
    }
}
