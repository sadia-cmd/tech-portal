#!/bin/bash
# Tech Portal — Backup Script
# Backs up database and wp-content directory
#
# Usage: ./scripts/backup.sh
# Cron:  0 2 * * * /root/workspace/tech-media-portal/scripts/backup.sh

set -euo pipefail

PROJECT_DIR="/root/workspace/tech-media-portal"
BACKUP_DIR="${PROJECT_DIR}/backups"
DATE=$(date +%Y%m%d_%H%M%S)
KEEP_DAYS=30

# Load environment
source "${PROJECT_DIR}/.env" 2>/dev/null || true

DB_NAME="${DB_NAME:-techportal}"
DB_USER="${DB_USER:-techportal}"
DB_PASS="${DB_PASSWORD:-}"

mkdir -p "${BACKUP_DIR}"

echo "[$(date)] Starting backup..."

# 1. Database backup
echo "  Backing up database: ${DB_NAME}"
mysqldump -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" \
    --single-transaction \
    --routines \
    --triggers \
    | gzip > "${BACKUP_DIR}/db_${DATE}.sql.gz"
echo "  Database backup: db_${DATE}.sql.gz"

# 2. wp-content backup (excluding large caches)
echo "  Backing up wp-content..."
tar czf "${BACKUP_DIR}/wp-content_${DATE}.tar.gz" \
    --exclude='*.log' \
    --exclude='cache/*' \
    --exclude='upgraded/*' \
    -C "${PROJECT_DIR}" \
    wp-content/
echo "  wp-content backup: wp-content_${DATE}.tar.gz"

# 3. Config backup (without secrets)
echo "  Backing up config..."
tar czf "${BACKUP_DIR}/config_${DATE}.tar.gz" \
    -C "${PROJECT_DIR}" \
    wp-config.php \
    .gitignore \
    docs/ \
    scripts/ \
    2>/dev/null || true
echo "  Config backup: config_${DATE}.tar.gz"

# 4. Clean old backups
echo "  Cleaning backups older than ${KEEP_DAYS} days..."
find "${BACKUP_DIR}" -name "*.gz" -mtime +${KEEP_DAYS} -delete 2>/dev/null || true

# 5. Summary
echo ""
echo "Backup complete!"
echo "  Location: ${BACKUP_DIR}/"
ls -lh "${BACKUP_DIR}/" | grep "${DATE}"
echo ""
echo "To restore database:"
echo "  gunzip < ${BACKUP_DIR}/db_${DATE}.sql.gz | mysql -u ${DB_USER} -p ${DB_NAME}"
echo ""
echo "To restore wp-content:"
echo "  tar xzf ${BACKUP_DIR}/wp-content_${DATE}.tar.gz -C ${PROJECT_DIR}/"
