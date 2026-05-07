<?php

/**
 * Backup Configuration
 * 
 * Configuration for automatic backup system that backs up
 * database and storage files to both local and cloud storage
 */

return [

    /**
     * Local Storage Configuration
     */
    'local' => [
        'enabled' => true,
        'path' => storage_path('backups'),
    ],

    /**
     * Cloud Storage Configuration (AWS S3)
     */
    'cloud' => [
        'enabled' => env('BACKUP_CLOUD_ENABLED', false),
        'disk' => 's3',
        'path' => env('BACKUP_CLOUD_PATH', 'backups'),
    ],

    /**
     * Backup Retention Policy
     * Keep backups for this many days
     */
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    /**
     * Schedule Configuration
     * When to run automatic backups
     */
    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', true),
        'frequency' => env('BACKUP_SCHEDULE_FREQUENCY', 'daily'), // daily, weekly, monthly
        'time' => env('BACKUP_SCHEDULE_TIME', '02:00'), // 24-hour format HH:MM
        'day' => env('BACKUP_SCHEDULE_DAY', 'Monday'), // For weekly: Monday-Sunday
        'date' => env('BACKUP_SCHEDULE_DATE', 1), // For monthly: 1-31
    ],

    /**
     * Email Notification
     */
    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATION_ENABLED', false),
        'email' => env('BACKUP_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS')),
        'on_success' => true,
        'on_failure' => true,
    ],

    /**
     * Logging Channel
     */
    'log_channel' => 'backup',

];
