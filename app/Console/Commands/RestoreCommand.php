<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class RestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore {file : Path to backup ZIP file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database and storage from a backup file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("❌ Backup file not found: {$file}");
            return Command::FAILURE;
        }

        if (!$this->confirm('⚠️  This will restore your database and storage. Continue?')) {
            $this->info('Restore cancelled.');
            return Command::SUCCESS;
        }

        try {
            $this->info('🔄 Starting restore process...');
            $this->newLine();

            // Extract backup
            $extractPath = storage_path('restore_temp_' . time());
            $this->info('📦 Extracting backup...');
            $this->extractZip($file, $extractPath);

            // Find the backup directory
            $backupDirs = array_filter(
                scandir($extractPath),
                fn($item) => strpos($item, 'backup_') === 0 && is_dir("{$extractPath}/{$item}")
            );

            if (empty($backupDirs)) {
                throw new \Exception('Invalid backup file structure');
            }

            $backupDir = reset($backupDirs);
            $restorePath = "{$extractPath}/{$backupDir}";

            // Restore database
            $databaseFile = "{$restorePath}/database.sql";
            if (file_exists($databaseFile)) {
                $this->info('🗄️  Restoring database...');
                $this->restoreDatabase($databaseFile);
                $this->info('✓ Database restored');
            }

            // Restore storage
            $storageDir = "{$restorePath}/storage";
            if (is_dir($storageDir)) {
                $this->info('📁 Restoring storage files...');
                $this->restoreStorage($storageDir);
                $this->info('✓ Storage files restored');
            }

            // Cleanup
            $this->info('🧹 Cleaning up...');
            $this->recursiveDelete($extractPath);

            $this->newLine();
            $this->info('✅ Restore completed successfully!');
            Log::channel('backup')->info('Backup restored successfully from: ' . $file);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Restore failed: ' . $e->getMessage());
            Log::channel('backup')->error('Backup restore failed: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }

    /**
     * Extract ZIP file
     */
    protected function extractZip($file, $path)
    {
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \Exception('Cannot open ZIP file');
        }

        if (!$zip->extractTo($path)) {
            throw new \Exception('Cannot extract ZIP file');
        }

        $zip->close();
    }

    /**
     * Restore database
     */
    protected function restoreDatabase($dumpFile)
    {
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');
        $dbHost = env('DB_HOST');

        $command = sprintf(
            'mysql -h %s -u %s -p%s %s < "%s"',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbName),
            $dumpFile
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \Exception("Database restore failed with exit code: $exitCode");
        }
    }

    /**
     * Restore storage files
     */
    protected function restoreStorage($sourceDir)
    {
        $targetDir = storage_path('app');

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Ask user to backup current storage first
        if ($this->confirm('Backup current storage files first?', true)) {
            $this->backupCurrentStorage($targetDir);
        }

        // Copy restored files
        $this->recursiveCopy($sourceDir, $targetDir);
    }

    /**
     * Backup current storage before restore
     */
    protected function backupCurrentStorage($storageDir)
    {
        $backupPath = storage_path('app_backup_' . now()->format('Y_m_d_H_i_s'));
        $this->recursiveCopy($storageDir, $backupPath);
        $this->info("✓ Current storage backed up to: {$backupPath}");
    }

    /**
     * Recursive copy
     */
    protected function recursiveCopy($src, $dst)
    {
        $dir = @opendir($src);
        @mkdir($dst);

        while (false !== ($file = @readdir($dir))) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    @copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        @closedir($dir);
    }

    /**
     * Recursive delete
     */
    protected function recursiveDelete($dir)
    {
        if (is_dir($dir)) {
            $files = @array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = "{$dir}/{$file}";
                if (is_dir($path)) {
                    $this->recursiveDelete($path);
                } else {
                    @unlink($path);
                }
            }
            @rmdir($dir);
        }
    }
}
