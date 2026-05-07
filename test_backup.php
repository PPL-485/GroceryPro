<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel');

// Test database connection
try {
    $db = $app->make('db');
    $result = $db->select('SELECT 1 as test');
    echo "✓ Database connected!\n";
    echo "Test result: " . $result[0]->test . "\n";
} catch (\Exception $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
}

// Test mysqldump
$dbName = env('DB_DATABASE');
$dbUser = env('DB_USERNAME');
$dbPassword = env('DB_PASSWORD');
$dbHost = env('DB_HOST');

echo "\nDatabase credentials:\n";
echo "Host: " . $dbHost . "\n";
echo "User: " . $dbUser . "\n";
echo "Pass: " . ($dbPassword ? '***' : 'EMPTY') . "\n";
echo "Database: " . $dbName . "\n";

// Find mysqldump
$mysqldumpPath = null;
$paths = [
    'C:\xampp\mysql\bin\mysqldump.exe',
    'C:\mysql\bin\mysqldump.exe',
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        $mysqldumpPath = $path;
        break;
    }
}

if (!$mysqldumpPath) {
    echo "\n✗ mysqldump not found!\n";
    exit(1);
}

echo "\n✓ mysqldump found at: " . $mysqldumpPath . "\n";

// Test mysqldump command
$dumpFile = 'test_dump.sql';
$command = sprintf(
    '"%s" -h %s -u %s -p%s %s > "%s" 2>&1',
    $mysqldumpPath,
    escapeshellarg($dbHost),
    escapeshellarg($dbUser),
    escapeshellarg($dbPassword),
    escapeshellarg($dbName),
    $dumpFile
);

echo "\nExecuting command...\n";
exec($command, $output, $exitCode);

echo "Exit code: " . $exitCode . "\n";
if (!empty($output)) {
    echo "Output: " . implode("\n", $output) . "\n";
}

if (file_exists($dumpFile)) {
    $size = filesize($dumpFile);
    echo "Dump file created: " . number_format($size) . " bytes\n";
    
    if ($size > 0) {
        echo "\n✓ Backup appears to be working!\n";
    } else {
        echo "\n✗ Dump file is empty!\n";
    }
    
    unlink($dumpFile);
} else {
    echo "\n✗ Dump file was not created!\n";
}
