#!/bin/bash
# ============================================
# Sync Workflow: Selesai Kerja (Push)
# ============================================
# Jalankan script ini SEBELUM pindah ke device lain
# Script ini akan:
# 1. Export database arors_v5
# 2. Commit & push arors_v5
# 3. Commit & push apm_rehab_v1
# 4. Stop Docker containers
# ============================================

set -e

PROJECTS_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ARORS_DIR="$PROJECTS_DIR/arors_v5"
APM_DIR="$PROJECTS_DIR/apm_rehab_v1"

echo "============================================"
echo " 🔄 SYNC: Selesai Kerja (Push ke GitHub)"
echo "============================================"
echo ""

# Step 1: Export database
echo "[STEP 1/4] Export database arors_v5..."
cd "$ARORS_DIR"
if docker compose ps --status running | grep -q arors_mysql; then
    docker compose exec app bash scripts/db-export.sh 2>/dev/null || echo "  ⚠️  DB export skipped (script not found or error)"
else
    echo "  ⚠️  MySQL container not running, skip DB export"
fi
echo ""

# Step 2: Push arors_v5
echo "[STEP 2/4] Push arors_v5..."
cd "$ARORS_DIR"
if [ -n "$(git status --porcelain)" ]; then
    git add .
    read -p "  Commit message untuk arors_v5: " MSG_ARORS
    git commit -m "sync: ${MSG_ARORS:-update dari $(hostname)}"
    git push origin master
    echo "  ✅ arors_v5 pushed!"
else
    echo "  ℹ️  Tidak ada perubahan di arors_v5"
fi
echo ""

# Step 3: Push apm_rehab_v1
echo "[STEP 3/4] Push apm_rehab_v1..."
cd "$APM_DIR"
if [ -n "$(git status --porcelain)" ]; then
    git add .
    read -p "  Commit message untuk apm_rehab_v1: " MSG_APM
    git commit -m "sync: ${MSG_APM:-update dari $(hostname)}"
    git push origin master
    echo "  ✅ apm_rehab_v1 pushed!"
else
    echo "  ℹ️  Tidak ada perubahan di apm_rehab_v1"
fi
echo ""

# Step 4: Stop Docker
echo "[STEP 4/4] Stop Docker containers..."
cd "$ARORS_DIR"
docker compose down
echo "  ✅ Docker stopped!"

echo ""
echo "============================================"
echo " ✅ SYNC SELESAI!"
echo " Sekarang bisa pindah ke device lain."
echo " Di device lain, jalankan: sync-pull.sh"
echo "============================================"
