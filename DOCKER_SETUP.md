# ARORS V5 — Docker Development Setup

## Prerequisites

Install di **kedua device** (PC & Laptop):
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/Mac)
- [Git](https://git-scm.com/)

## Setup Pertama Kali

```bash
# 1. Clone repository
git clone https://github.com/YOUR_USERNAME/arors_v5.git
cd arors_v5

# 2. Copy environment file
cp .env.example .env

# 3. Build & jalankan containers
docker compose up -d --build

# 4. Generate app key
docker compose exec app php artisan key:generate

# 5. Jalankan migration & seeder
docker compose exec app php artisan migrate --seed

# 6. Build assets (production)
docker compose exec app npm run build
```

Buka **http://localhost:8080** → App siap digunakan!

## Akses

| Service       | URL                     |
|---------------|-------------------------|
| App (Apache)  | http://localhost:8080    |
| Vite HMR      | http://localhost:5173    |
| MySQL         | localhost:3306           |

## Workflow Sehari-hari

### Mulai Kerja
```bash
docker compose up -d        # Start containers
```

### Selesai Kerja (Pindah Device)
```bash
# Export database
docker compose exec app bash scripts/db-export.sh

# Push ke Git
git add .
git commit -m "sync: [deskripsi perubahan]"
git push

# Stop containers
docker compose down
```

### Mulai di Device Lain
```bash
git pull                        # Pull kode + DB dump terbaru
docker compose up -d            # Start containers
docker compose exec app bash scripts/db-import.sh   # Import DB
docker compose exec app composer install             # Jika ada dependency baru
docker compose exec app npm install                  # Jika ada npm package baru
docker compose exec app php artisan migrate          # Jika ada migration baru
```

## Perintah Berguna

```bash
# Lihat status containers
docker compose ps

# Lihat logs
docker compose logs -f app

# Masuk ke container (bash)
docker compose exec app bash

# Jalankan artisan command
docker compose exec app php artisan [command]

# Rebuild container (setelah edit Dockerfile)
docker compose up -d --build

# Stop & hapus semua containers
docker compose down

# Stop & hapus TERMASUK volume database
docker compose down -v
```

## Troubleshooting

### Port sudah dipakai
Edit `docker-compose.yml`, ganti port di bagian `ports`.

### Permission error pada storage
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Composer/npm install gagal
```bash
docker compose exec app composer install
docker compose exec app npm install
```
