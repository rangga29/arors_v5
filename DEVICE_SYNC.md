# 🖥️ Setup PC Baru (Windows 11 Pro) — Step by Step

Panduan lengkap untuk setup PC baru dari nol agar bisa development
`arors_v5` (Laravel) dan `apm_rehab_v1` (React Electron) dengan Docker.

> **Situasi:** PC di rumah baru install Windows 11 Pro, belum ada apa-apa.
> Laptop di kantor sudah jalan. Kedua device sync via GitHub.

---

## TAHAP 1: Install Software di Windows

### 1.1 Install WSL (Windows Subsystem for Linux)

Buka **PowerShell sebagai Administrator**, jalankan:

```powershell
wsl --install -d Ubuntu
```

Setelah selesai, **restart PC**. Saat pertama kali buka Ubuntu, buat username & password.

### 1.2 Install Docker Desktop

1. Download dari: https://www.docker.com/products/docker-desktop/
2. Install, lalu buka Docker Desktop
3. Masuk ke **Settings → General** → pastikan ✅ **"Use the WSL 2 based engine"**
4. Masuk ke **Settings → Resources → WSL Integration** → aktifkan ✅ **Ubuntu**
5. Klik **Apply & Restart**

### 1.3 Install Node.js di Windows (untuk Electron Build)

1. Download dari: https://nodejs.org/ (pilih LTS)
2. Install dengan default settings
3. Verifikasi: buka CMD → ketik `node -v` dan `npm -v`

### 1.4 Install Git di Windows (jika belum ada)

1. Download dari: https://git-scm.com/
2. Install dengan default settings

### 1.5 Install Antigravity IDE

1. Download Antigravity IDE dari: https://www.antigravity.dev/
2. Install dengan default settings
3. Login/setup Antigravity sesuai instruksi
4. Buka folder project WSL: `\\wsl.localhost\Ubuntu\home\rangg\projects`

---

## TAHAP 2: Setup di WSL Ubuntu

Buka **terminal Ubuntu** (dari Start Menu cari "Ubuntu"), lalu jalankan satu per satu:

### 2.1 Install Node.js di WSL

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

Verifikasi:
```bash
node -v    # harus v20.x
npm -v     # harus 10.x
```

### 2.2 Setup Git

```bash
git config --global user.name "Rangga"
git config --global user.email "rangga29@users.noreply.github.com"
```

### 2.3 Clone Kedua Project

```bash
mkdir -p ~/projects
cd ~/projects

git clone https://github.com/rangga29/arors_v5.git
git clone https://github.com/rangga29/apm_rehab_v1.git
```

### 2.4 Setup Environment Files

```bash
cd ~/projects/arors_v5
cp .env.example .env

cd ~/projects/apm_rehab_v1
cp .env.example .env
```

### 2.5 Copy Sync Scripts ke folder projects

```bash
cp ~/projects/arors_v5/scripts/sync-push.sh ~/projects/sync-push.sh
cp ~/projects/arors_v5/scripts/sync-pull.sh ~/projects/sync-pull.sh
chmod +x ~/projects/sync-push.sh ~/projects/sync-pull.sh
```

---

## TAHAP 3: Jalankan Docker

### 3.1 Pastikan Docker Desktop Sudah Running

Buka Docker Desktop di Windows, tunggu sampai statusnya **"Running"**.

### 3.2 Pre-Check: Pastikan Vendor & Node Modules Ada

Sebelum start docker, pastikan folder vendor dan node_modules sudah ada:

```bash
cd ~/projects/arors_v5

# Cek vendor (harus ada folder vendor/)
ls -d vendor

# Cek node_modules (harus ada folder node_modules/)
ls -d node_modules
```

**PENTING:** Jika vendor atau node_modules tidak ada, clone dari GitHub dulu. Tidak perlu install manual karena sudah di-commit.

### 3.3 Build & Start Containers

```bash
cd ~/projects/arors_v5
docker compose up -d --build
```

Tunggu sampai selesai (pertama kali bisa 5-10 menit).

### 3.4 Verifikasi Container Jalan

```bash
docker compose ps
```

Pastikan status semua container **"Up"** dan **healthy** (khusus MySQL).

### 3.5 Setup Laravel (Pertama Kali)

```bash
cd ~/projects/arors_v5

# Generate app key (jika belum ada)
docker compose exec app php artisan key:generate

# Set permissions
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache

# Clear cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

### 3.6 Setup Database

**Opsi A: Fresh Migration (PC Baru)**
```bash
# Buat database baru (sudah dibuat otomatis oleh docker-compose)
# Jika perlu buat manual:
docker compose exec mysql mysql -u root -e "CREATE DATABASE IF NOT EXISTS regrsck_arors_v3;"

# Jalankan migration
docker compose exec app php artisan migrate --force
```

**Opsi B: Import dari Laptop (Jika sudah ada data)**
```bash
cd ~/projects
./sync-pull.sh
```

### 3.7 Build Frontend Assets

**Untuk Development (hot reload):**
```bash
# Vite sudah jalan otomatis di container vite (port 5173)
# Tidak perlu command tambahan di app container
```

**Untuk Production:**
```bash
docker compose exec app npm run build
```

### 3.8 Verifikasi Akhir

Buka browser:

| Service | URL | Yang Muncul |
|---------|-----|-------------|
| ARORS V5 | http://localhost:8080 | Halaman Laravel |
| APM Rehab Kiosk | http://localhost:5174 | Halaman React Kiosk |
| Vite HMR (ARORS) | http://localhost:5173 | Vite Dev Server |

**Test Manual:**
```bash
# Test koneksi database
docker compose exec app php artisan tinker --execute="echo DB::connection()->getPdo()->query('SELECT 1')->fetch();"

# Should output: 1
```

✅ Jika semua URL muncul dan test DB berhasil, **setup selesai!**

---

## TAHAP 4: Import Database dari Laptop

Jika di laptop sudah ada data, lakukan di **laptop** dulu:

```bash
# Di LAPTOP (kantor):
cd ~/projects
./sync-push.sh
```

Lalu di **PC rumah**:

```bash
# Di PC (rumah):
cd ~/projects
./sync-pull.sh
```

---

## Workflow Sehari-hari

### 🏢 Selesai Kerja di Laptop → Mau Pindah ke PC

```bash
cd ~/projects
./sync-push.sh
```

### 🏠 Sampai di Rumah → Mulai Kerja di PC

```bash
cd ~/projects
./sync-pull.sh
```

### 🏠 Selesai Kerja di PC → Mau Pindah ke Laptop

```bash
cd ~/projects
./sync-push.sh
```

### 🏢 Sampai di Kantor → Mulai Kerja di Laptop

```bash
cd ~/projects
./sync-pull.sh
```

---

## Build Electron (Desktop App)

Untuk build APM Rehab menjadi `.exe`:

1. Buka **PowerShell** di Windows
2. Jalankan:
   ```powershell
   \\wsl.localhost\Ubuntu\home\rangg\projects\apm_rehab_v1\build-windows.bat
   ```
3. Tunggu build selesai → app otomatis terbuka

---

## Quick Reference

| Perintah | Fungsi |
|----------|--------|
| `./sync-push.sh` | Selesai kerja, push ke GitHub |
| `./sync-pull.sh` | Mulai kerja, pull dari GitHub |
| `docker compose up -d` | Start Docker saja |
| `docker compose down` | Stop Docker saja |
| `docker compose ps` | Lihat status containers |
| `docker compose logs -f` | Lihat logs |
| `docker compose logs -f app` | Lihat logs app saja |

## URLs

| Service | URL |
|---------|-----|
| ARORS V5 (Laravel) | http://localhost:8080 |
| APM Rehab Kiosk (React) | http://localhost:5174 |
| Vite HMR (ARORS) | http://localhost:5173 |
| MySQL | localhost:3306 |

---

## Troubleshooting

### Docker tidak bisa start
- Pastikan Docker Desktop sudah running di Windows
- Pastikan WSL Integration untuk Ubuntu sudah aktif

### Port sudah dipakai
- Edit `docker-compose.yml`, ganti nomor port
- Atau matikan aplikasi yang pakai port tersebut:
  ```bash
  # Cari process yang pakai port
  netstat -ano | findstr :8080
  # Kill dengan taskkill /PID <number> /F
  ```

### Container jalan tapi localhost:8080 error 403 atau 500
**Ini adalah masalah umum!** Kemungkinan volume tidak ter-mount dengan benar.

**Cek isi folder di dalam container:**
```bash
docker exec arors_app ls -la /var/www/html/
```

**Jika folder kosong atau tidak ada `public`, `vendor`, `app`:**
```bash
# Stop dan restart container
docker compose down
docker compose up -d --build

# Cek lagi
docker exec arors_app ls -la /var/www/html/
```

**Jika masih bermasalah:**
```bash
# Hapus container dan volume sepenuhnya
docker compose down -v
docker compose up -d --build
```

### Permission error di Laravel
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

### MySQL tidak healthy / app tidak bisa konek ke DB
```bash
# Cek status MySQL
docker compose ps

# Cek logs MySQL
docker compose logs mysql

# Restart MySQL container
docker compose restart mysql
docker compose restart app

# Tunggu sebentar, lalu test lagi
docker compose exec app php artisan migrate:status
```

### npm install error di container (permission denied)
```bash
docker exec arors_app chown -R 1000:1000 /var/www/html/node_modules
```

### App key tidak ada / error encryption
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan config:clear
```

### Error: "Target class [session] does not exist" atau error service provider
```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan clear-compiled
docker compose exec app composer dump-autoload
```

### ingin fresh start (hapus semua data & container)
```bash
cd ~/projects/arors_v5
docker compose down -v
docker system prune -f
docker compose up -d --build
```

---

## Catatan Penting

1. **vendor dan node_modules sudah di-commit ke GitHub** - Tidak perlu install manual saat clone
2. **Volume mount menggunakan folder lokal** - Perubahan di lokal langsung terlihat di container
3. **Gunakan `docker compose` (bukan `docker-compose`)** - Format baru lebih baik
4. **Selalu cek `docker compose ps`** - Pastikan semua container running sebelum akses URL
