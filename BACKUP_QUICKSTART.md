# 🔐 GroceryPro Backup System - Quick Start Guide

## ✅ Selesai Diimplementasikan

Sistem backup otomatis telah dibuat dengan fitur lengkap untuk melindungi data GroceryPro Anda.

## 📋 File Yang Dibuat

### 1. **BackupService** (`app/Services/BackupService.php`)
- Handle semua logic backup (database + storage)
- Compress files ke ZIP
- Upload ke local dan cloud
- Auto-delete backup lama
- Comprehensive logging

### 2. **BackupCommand** (`app/Console/Commands/BackupCommand.php`)
- Artisan command untuk run backup manual
- User-friendly output

### 3. **BackupController** (`app/Http/Controllers/BackupController.php`)
- REST API untuk backup management
- List backups
- Manual trigger backup
- Download backup
- Delete backup
- View stats

### 4. **Configuration**
- `config/backup.php` - Backup configuration
- `.env` - Environment variables
- `config/logging.php` - Updated dengan backup channel

### 5. **Documentation**
- `BACKUP_DOCUMENTATION.md` - Full documentation
- `BACKUP_ENV_EXAMPLE.md` - Environment setup guide

### 6. **Routes**
- `routes/api.php` - Updated dengan backup API endpoints

## 🚀 Cara Menggunakan

### 1. Backup Manual (Artisan Command)

```bash
php artisan backup:run
```

Output:
```
🔄 Starting backup process...

✓ Backup completed successfully
📦 Backup name: backup_2026_05_05_14_30_45
```

### 2. Automatic Scheduled Backup

Default: **Setiap hari pukul 2 pagi**

Setup cronjob untuk Linux/Mac:
```bash
* * * * * cd /path/to/grocerypro && php artisan schedule:run >> /dev/null 2>&1
```

Atau Windows Task Scheduler:
1. Buka Task Scheduler
2. Buat task baru
3. Set trigger: setiap menit
4. Set action: `php artisan schedule:run`

### 3. API Endpoints

```bash
# Get backup list
GET /api/backups

# Create backup manually
POST /api/backups

# Get backup statistics
GET /api/backups/stats

# Download backup
GET /api/backups/{filename}/download

# Delete backup
DELETE /api/backups/{filename}
```

## ⚙️ Konfigurasi

Edit `.env` untuk customize:

```env
# Jadwal backup (daily, weekly, monthly)
BACKUP_SCHEDULE_FREQUENCY=daily

# Jam backup (24-hour format)
BACKUP_SCHEDULE_TIME=02:00

# Berapa hari backup disimpan
BACKUP_RETENTION_DAYS=30

# Cloud backup (S3)
BACKUP_CLOUD_ENABLED=false
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_BUCKET=your_bucket
```

## 📂 Backup Location

```
storage/backups/
├── backup_2026_05_05_14_30_45.zip
├── backup_2026_05_04_14_30_45.zip
└── ...
```

## 📊 Backup Contents

Setiap backup zip berisi:
- `database.sql` - MySQL dump
- `storage/` - Semua file storage

## 📝 Logging

Lihat log backup:
```bash
# Real-time
tail -f storage/logs/backup.log

# Full path
storage/logs/backup.log
```

## 🔧 Features

✅ **Full Backup** - Database + Storage files
✅ **Multi-Storage** - Local + AWS S3 cloud
✅ **Automatic Scheduling** - Daily/Weekly/Monthly
✅ **Auto Cleanup** - Hapus backup lama otomatis
✅ **Compression** - ZIP format untuk hemat storage
✅ **Logging** - Semua aktivitas tercatat
✅ **API Management** - REST API untuk manage backups
✅ **Manual Trigger** - Backup kapan saja via command atau API
✅ **Error Handling** - Comprehensive error handling

## 📌 Next Steps

1. **Test backup manual:**
   ```bash
   php artisan backup:run
   ```

2. **Check log:**
   ```bash
   tail -f storage/logs/backup.log
   ```

3. **Setup cronjob** untuk automatic backup

4. **Configure cloud storage** (opsional)

5. **Setup notifications** (opsional)

## ⚠️ Important Notes

- Pastikan `mysqldump` terinstall (usually with MySQL)
- Pastikan folder `storage/backups/` writable
- Test backup restore setidaknya sekali sebulan
- Monitor disk space - backups bisa besar

## 🆘 Troubleshooting

### Error: "mysqldump not found"
- Add MySQL bin folder ke system PATH
- Atau use full path di command

### Backup too large
- Kurangi `BACKUP_RETENTION_DAYS`
- Exclude large files

### Cloud upload fails
- Verify AWS credentials
- Check IAM permissions

## 📚 Full Documentation

Lihat `BACKUP_DOCUMENTATION.md` untuk dokumentasi lengkap termasuk:
- Restore procedure
- Advanced configuration
- Performance tips
- Backup schedule examples

## 💡 Tips

- Jalankan backup di off-peak hours (tengah malam)
- Monitor backup logs regularly
- Keep backups di multiple locations
- Test restore setidaknya monthly
- Document your restore procedure

---

**Version:** 1.0.0
**Created:** 5 May 2026
**Status:** ✅ Ready to Use
