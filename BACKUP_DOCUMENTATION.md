# Backup System Documentation

Sistem backup otomatis untuk GroceryPro yang mencakup database dan file storage dengan penyimpanan di local dan cloud (AWS S3).

## Fitur

✅ **Backup Database** - MySQL dump otomatis
✅ **Backup Storage** - Semua file di `storage/app`
✅ **Multi-Storage** - Local dan AWS S3
✅ **Auto Schedule** - Daily, Weekly, atau Monthly
✅ **Retention Policy** - Auto delete backup lama
✅ **Logging** - Semua aktivitas tercatat di `storage/logs/backup.log`
✅ **ZIP Archive** - Kompres semua data backup

## Instalasi

### 1. Environment Setup

Tambahkan konfigurasi backup ke `.env`:

```env
# Backup Schedule
BACKUP_SCHEDULE_ENABLED=true
BACKUP_SCHEDULE_FREQUENCY=daily        # daily, weekly, monthly
BACKUP_SCHEDULE_TIME=02:00             # HH:MM format (24-hour)
BACKUP_SCHEDULE_DAY=Monday             # Untuk weekly (Monday-Sunday)
BACKUP_SCHEDULE_DATE=1                 # Untuk monthly (1-31)

# Backup Retention
BACKUP_RETENTION_DAYS=30               # Hapus backup lebih dari 30 hari

# Cloud Storage (AWS S3)
BACKUP_CLOUD_ENABLED=false             # Enable/disable backup ke S3
BACKUP_CLOUD_PATH=backups              # Folder di S3

# Notifikasi
BACKUP_NOTIFICATION_ENABLED=false
BACKUP_NOTIFICATION_EMAIL=admin@example.com
```

### 2. AWS S3 Configuration (Opsional)

Jika menggunakan cloud backup, pastikan AWS credentials sudah dikonfigurasi:

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your-bucket-name
```

### 3. Database Setup

Pastikan `mysqldump` terinstall:

**Windows:**
```bash
# MySQLdump biasanya sudah terinstall dengan MySQL
# Pastikan MySQL bin folder ada di PATH
```

**Linux/Mac:**
```bash
# Install jika belum ada
sudo apt-get install mysql-client  # Ubuntu/Debian
brew install mysql-client         # macOS
```

## Penggunaan

### Manual Backup (Command Line)

Jalankan backup secara manual kapan saja:

```bash
php artisan backup:run
```

Output:
```
🔄 Starting backup process...

✓ Backup completed successfully
📦 Backup name: backup_2026_05_05_14_30_45
```

### Automatic Scheduled Backup

Backup otomatis akan berjalan sesuai jadwal yang dikonfigurasi. Untuk ini, setup cronjob:

**Linux/Mac:**
```bash
# Tambahkan ke crontab
* * * * * cd /path/to/grocerypro && php artisan schedule:run >> /dev/null 2>&1
```

**Windows (Task Scheduler):**
1. Buka Task Scheduler
2. Buat task baru
3. Set trigger: setiap menit
4. Set action: `php artisan schedule:run`

### Monitoring Logs

Lihat log backup:

```bash
# Real-time
tail -f storage/logs/backup.log

# Atau lihat file langsung
storage/logs/backup.log
```

## Konfigurasi Detail

### config/backup.php

```php
return [
    // Local storage
    'local' => [
        'enabled' => true,
        'path' => storage_path('backups'),  // Folder penyimpanan
    ],

    // Cloud storage (S3)
    'cloud' => [
        'enabled' => env('BACKUP_CLOUD_ENABLED', false),
        'disk' => 's3',
        'path' => env('BACKUP_CLOUD_PATH', 'backups'),
    ],

    // Berapa hari backup disimpan
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    // Jadwal backup
    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', true),
        'frequency' => env('BACKUP_SCHEDULE_FREQUENCY', 'daily'),
        'time' => env('BACKUP_SCHEDULE_TIME', '02:00'),
        'day' => env('BACKUP_SCHEDULE_DAY', 'Monday'),
        'date' => env('BACKUP_SCHEDULE_DATE', 1),
    ],
];
```

## Backup Schedule Examples

### Daily at 2 AM
```env
BACKUP_SCHEDULE_FREQUENCY=daily
BACKUP_SCHEDULE_TIME=02:00
```

### Every Monday at 3 AM
```env
BACKUP_SCHEDULE_FREQUENCY=weekly
BACKUP_SCHEDULE_DAY=Monday
BACKUP_SCHEDULE_TIME=03:00
```

### First day of month at 4 AM
```env
BACKUP_SCHEDULE_FREQUENCY=monthly
BACKUP_SCHEDULE_DATE=1
BACKUP_SCHEDULE_TIME=04:00
```

## File Structure

```
storage/
├── backups/                    # Folder backup
│   ├── backup_2026_05_05_14_30_45.zip
│   ├── backup_2026_05_04_14_30_45.zip
│   └── temp_2026_05_05_14_30_45/    # Temporary folder (auto-deleted)
└── logs/
    └── backup.log              # Log file
```

## Backup Contents

Setiap file backup berisi:

```
backup_2026_05_05_14_30_45.zip
├── backup_2026_05_05_14_30_45/
│   ├── database.sql            # Database dump
│   └── storage/                # Semua file di storage/app
│       ├── uploads/
│       ├── thumbnails/
│       └── ...
```

## Log Format

```
[2026-05-05 14:30:45] local.INFO: Starting backup process: backup_2026_05_05_14_30_45
[2026-05-05 14:30:46] local.INFO: Database backup completed
[2026-05-05 14:30:47] local.INFO: Storage files backup completed
[2026-05-05 14:30:48] local.INFO: ZIP archive created: /storage/backups/backup_2026_05_05_14_30_45.zip
[2026-05-05 14:30:49] local.INFO: Backup uploaded to local storage
[2026-05-05 14:30:50] local.INFO: Temporary files cleaned up
[2026-05-05 14:30:51] local.INFO: Old backups deleted
[2026-05-05 14:30:51] local.INFO: ✓ Backup process completed successfully: backup_2026_05_05_14_30_45
```

## Troubleshooting

### Error: "mysqldump not found"

**Solusi:**
- Pastikan MySQL terinstall dengan benar
- Tambahkan MySQL bin folder ke PATH system
- Atau gunakan full path di command: `C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump`

### Backup file terlalu besar

**Solusi:**
- Kurangi `BACKUP_RETENTION_DAYS` untuk lebih sering hapus backup lama
- Implementasikan incremental backup
- Exclude folder besar dari backup

### Cloud upload gagal

**Solusi:**
- Verifikasi AWS credentials di `.env`
- Pastikan bucket sudah ada
- Check IAM permissions

### Storage penuh

**Solusi:**
- Kurangi retention days
- Hapus backup manual yang tidak perlu
- Pindahkan backup lama ke external storage

## Advanced Usage

### Restore dari Backup

```bash
# 1. Extract backup
unzip storage/backups/backup_2026_05_05_14_30_45.zip

# 2. Restore database
mysql -u root -p grocerypro < backup_2026_05_05_14_30_45/database.sql

# 3. Restore storage files
cp -r backup_2026_05_05_14_30_45/storage/* storage/app/
```

### Custom Backup Schedule

Edit `app/Console/Kernel.php` untuk custom scheduling:

```php
protected function schedule(Schedule $schedule): void
{
    // Backup setiap hari jam 2 pagi
    $schedule->command('backup:run')->dailyAt('02:00');
    
    // Backup setiap Senin jam 3 pagi
    $schedule->command('backup:run')->weeklyOn(1, '03:00');
    
    // Backup tanggal 1 bulan jam 4 pagi
    $schedule->command('backup:run')->monthlyOn(1, '04:00');
}
```

## Performance Tips

1. **Jalankan backup di off-peak hours** (tengah malam)
2. **Gunakan compression** (ZIP) untuk menghemat storage
3. **Set retention policy** untuk auto-cleanup
4. **Monitor backup logs** secara berkala
5. **Test restore** setidaknya sekali sebulan

## Files Created

- `app/Services/BackupService.php` - Backup logic service
- `app/Console/Commands/BackupCommand.php` - Artisan command
- `config/backup.php` - Configuration file
- `app/Console/Kernel.php` - Updated dengan scheduling

## Support

Untuk issues atau pertanyaan, check log file di:
```
storage/logs/backup.log
```

---

**Dibuat pada:** 5 May 2026
**Version:** 1.0.0
