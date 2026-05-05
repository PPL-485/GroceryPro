# Backup Configuration Example

# Enable/Disable automatic backup scheduling
BACKUP_SCHEDULE_ENABLED=true

# Backup frequency: daily, weekly, or monthly
BACKUP_SCHEDULE_FREQUENCY=daily

# Backup time in 24-hour format (HH:MM)
BACKUP_SCHEDULE_TIME=02:00

# Day of week for weekly backup (Monday-Sunday)
BACKUP_SCHEDULE_DAY=Monday

# Date of month for monthly backup (1-31)
BACKUP_SCHEDULE_DATE=1

# Number of days to retain backups before auto-deletion
BACKUP_RETENTION_DAYS=30

# Enable/Disable cloud storage backup (AWS S3)
BACKUP_CLOUD_ENABLED=false

# S3 bucket path for backups
BACKUP_CLOUD_PATH=backups

# Enable/Disable backup completion notifications
BACKUP_NOTIFICATION_ENABLED=false

# Email address for backup notifications
BACKUP_NOTIFICATION_EMAIL=admin@example.com

# AWS Configuration (if using cloud backup)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
