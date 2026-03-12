#!/bin/bash
# ============================================
# Sync Workflow: Mulai Kerja (Pull)
# ============================================
# Jalankan script ini SETELAH pindah ke device baru
# Script ini akan:
# 1. Pull arors_v5 dan apm_rehab_v1
# 2. Start Docker containers
# 3. Import database (jika ada update)
# 4. Install dependencies baru (jika ada)
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
echo "[STEP 1/4] Pull dari GitHub..."
echo "  📦 arors_v5..."
cd "$ARORS_DIR"
git pull origin master
echo ""
echo "  📦 apm_rehab_v1..."
cd "$APM_DIR"
git pull origin master
echo ""

# Step 2: Start Docker
echo "[STEP 2/4] Start Docker containers..."
cd "$ARORS_DIR"
docker compose up -d --build
echo ""

# Step 3: Wait for MySQL & Import DB
echo "[STEP 3/4] Import database..."
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

docker compose exec app bash scripts/db-import.sh 2>/dev/null || echo "  ⚠️  DB import skipped (no dump file or error)"
echo ""

# Step 4: Install dependencies
echo "[STEP 4/4] Check & install dependencies..."
echo "  📦 arors_v5 (composer)..."
docker compose exec app composer install --no-interaction 2>/dev/null || true
echo "  📦 arors_v5 (npm)..."
docker compose exec app npm install 2>/dev/null || true
echo "  📦 arors_v5 (migrate)..."
docker compose exec app php artisan migrate --force 2>/dev/null || true
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
