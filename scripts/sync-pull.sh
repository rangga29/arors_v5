#!/bin/bash
# ============================================
# Sync Workflow: Mulai Kerja (Pull)
# ============================================
# Jalankan script ini SETELAH pindah ke device baru
# Script ini akan:
# 1. Pull arors_v5 dan apm_rehab_v1
# 2. Start Docker containers
# 3. Verify volume mount
# 4. Set permissions & clear cache
# 5. Import database (jika ada update)
# ============================================

set -e

PROJECTS_DIR="$(cd "$(dirname "$0")" && pwd)"
ARORS_DIR="$PROJECTS_DIR/arors_v5"
APM_DIR="$PROJECTS_DIR/apm_rehab_v1"

echo "============================================"
echo " 🔄 SYNC: Mulai Kerja (Pull dari GitHub)"
echo "============================================"
echo ""

# Step 1: Pull both repos
echo "[STEP 1/5] Pull dari GitHub..."
echo "  📦 arors_v5..."
cd "$ARORS_DIR"
git pull origin master
echo ""
echo "  📦 apm_rehab_v1..."
cd "$APM_DIR"
git pull origin master
echo ""

# Step 2: Start Docker
echo "[STEP 2/5] Start Docker containers..."
cd "$ARORS_DIR"
docker compose up -d --build
echo ""

# Step 3: Verify Volume Mount
echo "[STEP 3/5] Verify volume mount..."
CONTAINER_FILES=$(docker exec arors_app ls -la /var/www/html/ 2>/dev/null | wc -l)
if [ "$CONTAINER_FILES" -lt 20 ]; then
    echo "  ⚠️  Volume mount bermasalah! Folder container hampir kosong."
    echo "  🔄 Me-restart container..."
    docker compose down
    docker compose up -d --build
    sleep 3

    # Cek ulang
    CONTAINER_FILES=$(docker exec arors_app ls -la /var/www/html/ 2>/dev/null | wc -l)
    if [ "$CONTAINER_FILES" -lt 20 ]; then
        echo "  ❌ Gagal mount volume. Cek docker-compose.yml dan folder lokal."
        exit 1
    fi
fi
echo "  ✅ Volume mount OK!"
echo ""

# Step 4: Wait for MySQL, Set Permissions & Clear Cache
echo "[STEP 4/5] Setup Laravel..."
echo "  Menunggu MySQL ready..."
sleep 5

# Check if MySQL is healthy
RETRIES=0
while [ $RETRIES -lt 12 ]; do
    if docker compose exec mysql mysqladmin ping -h localhost --silent 2>/dev/null; then
        break
    fi
    RETRIES=$((RETRIES + 1))
    echo "  Menunggu MySQL... ($RETRIES/12)"
    sleep 5
done

# Set permissions
echo "  🔧 Set permissions..."
docker compose exec app chmod -R 775 storage bootstrap/cache 2>/dev/null || true
docker compose exec app chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Clear cache
echo "  🧹 Clear Laravel cache..."
docker compose exec app php artisan config:clear 2>/dev/null || true
docker compose exec app php artisan cache:clear 2>/dev/null || true
docker compose exec app php artisan route:clear 2>/dev/null || true
docker compose exec app php artisan view:clear 2>/dev/null || true
docker compose exec app php artisan clear-compiled 2>/dev/null || true
echo ""

# Step 5: Import DB & Install dependencies
echo "[STEP 5/5] Import database & check dependencies..."
docker compose exec app bash scripts/db-import.sh 2>/dev/null || echo "  ⚠️  DB import skipped (no dump file or error)"

echo "  📦 Check composer..."
docker compose exec app composer install --no-interaction 2>/dev/null || true
echo "  📦 Check npm..."
docker compose exec app npm install 2>/dev/null || true
echo "  📦 Check migration..."
docker compose exec app php artisan migrate --force 2>/dev/null || true
echo ""

# Final verification
echo "============================================"
echo " 📊 Container Status:"
docker compose ps
echo ""

echo "============================================"
echo " ✅ SYNC SELESAI! Semua siap digunakan."
echo "============================================"
echo ""
echo " 🌐 ARORS V5:        http://localhost:8080"
echo " 🖥️  APM Rehab Kiosk: http://localhost:5174"
echo " 📊 MySQL:           localhost:3306"
echo ""
echo " Selamat bekerja! 🚀"
echo "============================================"
