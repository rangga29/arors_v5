#!/bin/bash
# ============================================
# Import database dari file SQL (sync dari Git)
# ============================================
# Usage: docker compose exec app bash scripts/db-import.sh

set -e

DUMP_FILE="/var/www/html/docker/mysql/init/database.sql"

if [ ! -f "$DUMP_FILE" ]; then
    echo "❌ File tidak ditemukan: docker/mysql/init/database.sql"
    echo "💡 Pastikan sudah git pull terlebih dahulu."
    exit 1
fi

echo "📦 Importing database..."

mysql -h mysql -u root ${DB_DATABASE:-regrsck_arors_v3} < "$DUMP_FILE"

echo "✅ Database imported dari: docker/mysql/init/database.sql"
