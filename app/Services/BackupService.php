<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ZipArchive;

class BackupService
{
    protected string $backupPath;
    protected string $logChannel = 'backup';
    protected string $timestamp;
    protected string $fileName;

    public function __construct()
    {
        $this->timestamp = now()->format('Y_m_d_H_i_s');
        $this->fileName = "backup_{$this->timestamp}";
        $this->backupPath = storage_path('backups');
        
        // Create backup directory if not exists
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Execute full backup (database + storage)
     */
    public function backup()
    {
        try {
            Log::channel($this->logChannel)->info("Starting backup process: {$this->fileName}");

            // Create temporary directory for backup files
            $tempDir = storage_path("backups/temp_{$this->timestamp}");
            mkdir($tempDir, 0755, true);

            // Backup database
            $this->backupDatabase($tempDir);
            Log::channel($this->logChannel)->info("Database backup completed");

            // Backup storage files
            $this->backupStorage($tempDir);
            Log::channel($this->logChannel)->info("Storage files backup completed");

            // Create zip file
            $zipPath = $this->createZip($tempDir);
            Log::channel($this->logChannel)->info("ZIP archive created: {$zipPath}");

            // Upload to local storage
            $this->uploadToLocal($zipPath);
            Log::channel($this->logChannel)->info("Backup uploaded to local storage");

            // Upload to cloud (S3)
            if (config('backup.cloud.enabled')) {
                $this->uploadToCloud($zipPath);
                Log::channel($this->logChannel)->info("Backup uploaded to cloud storage");
            }

            // Clean up temporary files
            $this->cleanup($tempDir, $zipPath);
            Log::channel($this->logChannel)->info("Temporary files cleaned up");

            // Clean old backups
            $this->deleteOldBackups();
            Log::channel($this->logChannel)->info("Old backups deleted");

            Log::channel($this->logChannel)->info("✓ Backup process completed successfully: {$this->fileName}");

            return [
                'success' => true,
                'message' => 'Backup completed successfully',
                'fileName' => $this->fileName
            ];

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error("Backup failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
                'error' => $e
            ];
        }
    }

    /**
     * Backup database
     */
    protected function backupDatabase(string $tempDir): string
    {
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');
        $dbHost = env('DB_HOST');
        $dumpFile = "{$tempDir}/database.sql";

        // Determine mysqldump path based on OS
        $mysqldumpPath = $this->findMysqldump();

        // Build command - only add -p flag if password is not empty
        $passwordFlag = !empty($dbPassword) ? "-p{$dbPassword}" : '';
        
        $command = sprintf(
            '"%s" -h %s -u %s %s %s > "%s" 2>&1',
            $mysqldumpPath,
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            $passwordFlag,
            escapeshellarg($dbName),
            $dumpFile
        );

        // Execute mysqldump command
        exec($command, $output, $exitCode);

        // mysqldump may return warnings but still create the dump
        // Only fail if exit code is > 1 or if file is empty
        if ($exitCode > 1 && $exitCode !== 0) {
            throw new \Exception("Database backup failed with exit code: $exitCode. Output: " . implode("\n", $output));
        }

        if (!file_exists($dumpFile) || filesize($dumpFile) === 0) {
            throw new \Exception("Database dump file is empty or not created");
        }

        return $dumpFile;
    }

    /**
     * Backup storage files
     */
    protected function backupStorage(string $tempDir): void
    {
        $storageDir = storage_path('app');
        $backupStorageDir = "{$tempDir}/storage";

        if (is_dir($storageDir)) {
            // Copy storage directory recursively
            $this->recursiveCopy($storageDir, $backupStorageDir);
        }
    }

    /**
     * Create ZIP archive
     */
    protected function createZip(string $tempDir): string
    {
        $zipPath = "{$this->backupPath}/{$this->fileName}.zip";
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Failed to create ZIP archive");
        }

        // Add files from temp directory
        $this->addFilesToZip($zip, $tempDir, basename($tempDir));

        $zip->close();

        if (!file_exists($zipPath)) {
            throw new \Exception("ZIP file not created");
        }

        return $zipPath;
    }

    /**
     * Add files to ZIP recursively
     */
    protected function addFilesToZip(ZipArchive $zip, string $dir, string $prefix): void
    {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = "{$dir}/{$file}";
            $arcPath = "{$prefix}/{$file}";

            if (is_dir($filePath)) {
                $this->addFilesToZip($zip, $filePath, $arcPath);
            } else {
                $zip->addFile($filePath, $arcPath);
            }
        }
    }

    /**
     * Upload to local storage
     */
    protected function uploadToLocal(string $zipPath): void
    {
        $filename = basename($zipPath);
        $localPath = config('backup.local.path');
        
        // Create local backup directory if it doesn't exist
        if (!is_dir($localPath)) {
            mkdir($localPath, 0755, true);
        }

        copy($zipPath, "{$localPath}/{$filename}");
    }

    /**
     * Upload to cloud (S3)
     */
    protected function uploadToCloud(string $zipPath): void
    {
        try {
            $filename = basename($zipPath);
            $cloudPath = config('backup.cloud.path', 'backups');
            $disk = Storage::disk('s3');

            $disk->put("{$cloudPath}/{$filename}", file_get_contents($zipPath));

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->warning("Cloud upload failed: " . $e->getMessage());
            // Don't throw exception - backup still succeeded locally
        }
    }

    /**
     * Clean up temporary files
     */
    protected function cleanup(string $tempDir, string $zipPath): void
    {
        // Delete temporary directory
        $this->recursiveDelete($tempDir);

        // Note: We don't delete the zip file here because it's already
        // been copied to the local backup path (storage/backups/)
        // The retention policy will handle deleting old backups
    }

    /**
     * Delete old backups
     */
    protected function deleteOldBackups(): void
    {
        $retention = config('backup.retention_days', 30);
        $localPath = config('backup.local.path');
        $cutoffDate = Carbon::now()->subDays($retention);

        // Delete local backups
        if (is_dir($localPath)) {
            $files = scandir($localPath);
            foreach ($files as $file) {
                if (strpos($file, 'backup_') === 0) {
                    $filePath = "{$localPath}/{$file}";
                    if (filemtime($filePath) < $cutoffDate->timestamp) {
                        unlink($filePath);
                        Log::channel($this->logChannel)->info("Deleted old backup: {$file}");
                    }
                }
            }
        }

        // Delete cloud backups
        if (config('backup.cloud.enabled')) {
            try {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('s3');
                $cloudPath = config('backup.cloud.path', 'backups');
                $listing = $disk->listContents($cloudPath, false);

                foreach ($listing as $item) {
                    if (strpos($item->path(), 'backup_') !== false && $item->isFile()) {
                        if ($item->lastModified() < $cutoffDate->timestamp) {
                            $disk->delete($item->path());
                            Log::channel($this->logChannel)->info("Deleted old cloud backup: " . $item->path());
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::channel($this->logChannel)->warning("Failed to delete cloud backups: " . $e->getMessage());
            }
        }
    }

    /**
     * Recursive copy directory
     */
    protected function recursiveCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    /**
     * Recursive delete directory
     */
    protected function recursiveDelete(string $dir): void
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = "{$dir}/{$file}";
                if (is_dir($path)) {
                    $this->recursiveDelete($path);
                } else {
                    unlink($path);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Get list of backups
     */
    public function getBackupList()
    {
        $backups = [];
        $localPath = config('backup.local.path');

        if (is_dir($localPath)) {
            $files = scandir($localPath, SCANDIR_SORT_DESCENDING);
            foreach ($files as $file) {
                if (strpos($file, 'backup_') === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $backups[] = [
                        'name' => $file,
                        'size' => filesize("{$localPath}/{$file}"),
                        'date' => filemtime("{$localPath}/{$file}"),
                        'path' => "{$localPath}/{$file}"
                    ];
                }
            }
        }

        return $backups;
    }

    /**
     * Find mysqldump path based on OS
     */
    protected function findMysqldump()
    {
        // Windows paths to check
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $paths = [
                'C:\xampp\mysql\bin\mysqldump.exe',
                'C:\mysql\bin\mysqldump.exe',
                'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
                'C:\Program Files\MySQL\MySQL Server 5.7\bin\mysqldump.exe',
                'C:\Program Files (x86)\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
                'C:\Program Files (x86)\MySQL\MySQL Server 5.7\bin\mysqldump.exe',
            ];

            foreach ($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        } else {
            // Unix/Linux paths
            $paths = [
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
            ];

            foreach ($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        // Fallback to just 'mysqldump' hoping it's in PATH
        return 'mysqldump';
    }
}
