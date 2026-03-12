#!/bin/bash
# ============================================
# Export database ke file SQL untuk sync via Git
# ============================================
# Usage: docker compose exec app bash scripts/db-export.sh

set -e

DUMP_FILE="/var/www/html/docker/mysql/init/database.sql"

echo "📦 Exporting database..."

mysqldump -h mysql -u root \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    ${DB_DATABASE:-regrsck_arors_v3} > "$DUMP_FILE"

echo "✅ Database exported ke: docker/mysql/init/database.sql"
echo "📝 Jangan lupa: git add, commit, push!"
