<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function backup()
    {
        $databaseName = env('DB_DATABASE');
        $userName = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');
        
        $date = now()->format('Y-m-d_H-i-s');
        $fileName = "backup_{$databaseName}_{$date}.sql";
        $path = storage_path("app/" . $fileName);

        // Construct the mysqldump command
        $passwordString = $password ? "-p\"{$password}\"" : "";
        $command = "mysqldump --user={$userName} {$passwordString} --host={$host} {$databaseName} > \"{$path}\" 2>&1";
        
        $returnVar = NULL;
        $output = NULL;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !File::exists($path) || File::size($path) === 0) {
            // Clean up empty file if created
            if (File::exists($path)) {
                File::delete($path);
            }
            
            // Output error message for debugging
            $errorMsg = implode("\n", $output);
            return back()->with('error', 'Backup failed. Error: ' . $errorMsg . '. Ensure mysqldump is installed and accessible in the system PATH.');
        }

        return Response::download($path)->deleteFileAfterSend(true);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:50000' // Limit size to 50MB, accept any file since .sql might not be recognized as sql mime
        ]);

        $databaseName = env('DB_DATABASE');
        $userName = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');
        
        $file = $request->file('file');
        $path = $file->getRealPath();

        // Construct the mysql command
        $passwordString = $password ? "-p\"{$password}\"" : "";
        $command = "mysql --user={$userName} {$passwordString} --host={$host} {$databaseName} < \"{$path}\" 2>&1";
        
        $returnVar = NULL;
        $output = NULL;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMsg = implode("\n", $output);
            return back()->with('error', 'Restore failed. Error: ' . $errorMsg . '. Ensure mysql is installed and accessible in the system PATH.');
        }

        return back()->with('success', 'Database restored successfully!');
    }
}
