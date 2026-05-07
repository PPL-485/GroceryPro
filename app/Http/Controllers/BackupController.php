<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Get list of all backups
     */
    public function index()
    {
        $backups = $this->backupService->getBackupList();
        
        // Format backup data
        $formattedBackups = array_map(function ($backup) {
            return [
                'name' => $backup['name'],
                'size' => $this->formatBytes($backup['size']),
                'sizeBytes' => $backup['size'],
                'date' => date('Y-m-d H:i:s', $backup['date']),
                'timestamp' => $backup['date'],
                'path' => $backup['path']
            ];
        }, $backups);

        return response()->json([
            'success' => true,
            'data' => $formattedBackups,
            'count' => count($formattedBackups)
        ]);
    }

    /**
     * Trigger backup manually and return ZIP download
     */
    public function backup(Request $request)
    {
        Log::info('Manual backup triggered by user: ' . auth()->id());
        
        $result = $this->backupService->backup();

        if ($result['success']) {
            $backupFile = storage_path('backups/' . $result['fileName'] . '.zip');

            if (!file_exists($backupFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup completed, but backup file is missing.'
                ], 500);
            }

            return response()->download($backupFile);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * Trigger backup manually
     */
    public function create(Request $request)
    {
        Log::info('Manual backup triggered by user: ' . auth()->id());
        
        $result = $this->backupService->backup();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'backupName' => $result['fileName']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }

    /**
     * Download a backup file
     */
    public function download(string $filename)
    {
        $backupPath = config('backup.local.path');
        $filePath = "{$backupPath}/{$filename}";

        // Security check - prevent directory traversal
        if (!file_exists($filePath) || strpos(realpath($filePath), realpath($backupPath)) !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found'
            ], 404);
        }

        Log::info('Backup downloaded: ' . $filename . ' by user: ' . auth()->id());

        return response()->download($filePath);
    }

    /**
     * Delete a backup file
     */
    public function destroy(string $filename)
    {
        $backupPath = config('backup.local.path');
        $filePath = "{$backupPath}/{$filename}";

        // Security check
        if (!file_exists($filePath) || strpos(realpath($filePath), realpath($backupPath)) !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found'
            ], 404);
        }

        try {
            unlink($filePath);
            Log::info('Backup deleted: ' . $filename . ' by user: ' . auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete backup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup'
            ], 500);
        }
    }

    /**
     * Get backup statistics
     */
    public function stats()
    {
        $backups = $this->backupService->getBackupList();
        $totalSize = array_sum(array_column($backups, 'size'));

        return response()->json([
            'success' => true,
            'data' => [
                'totalBackups' => count($backups),
                'totalSize' => $this->formatBytes($totalSize),
                'totalSizeBytes' => $totalSize,
                'latestBackup' => !empty($backups) ? [
                    'name' => $backups[0]['name'],
                    'date' => date('Y-m-d H:i:s', $backups[0]['date']),
                    'size' => $this->formatBytes($backups[0]['size'])
                ] : null,
                'oldestBackup' => !empty($backups) ? [
                    'name' => end($backups)['name'],
                    'date' => date('Y-m-d H:i:s', end($backups)['date']),
                ] : null,
            ]
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int|float $bytes, int $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
