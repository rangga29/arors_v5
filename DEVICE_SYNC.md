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

### 1.5 Install VS Code + Antigravity

1. Download VS Code dari: https://code.visualstudio.com/
2. Install dengan default settings
3. Buka VS Code → Extensions (Ctrl+Shift+X)
4. Cari dan install **Antigravity** extension
5. Login/setup Antigravity sesuai instruksi

> **Tips:** Aktifkan **Settings Sync** di VS Code (ikon akun kiri bawah → Turn on Settings Sync → login GitHub).
> Ini akan otomatis sync extensions, settings, dan keybindings antar device!

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

### 3.2 Build & Start Containers

```bash
cd ~/projects/arors_v5
docker compose up -d --build
```

Tunggu sampai selesai (pertama kali bisa 5-10 menit).

### 3.3 Setup Laravel (Pertama Kali)

```bash
# Generate app key
docker compose exec app php artisan key:generate

# Jalankan migration & seeder
docker compose exec app php artisan migrate --seed

# Build frontend assets
docker compose exec app npm run build
```

### 3.4 Verifikasi

Buka browser:

| Service | URL | Yang Muncul |
|---------|-----|-------------|
| ARORS V5 | http://localhost:8080 | Halaman Laravel |
| APM Rehab Kiosk | http://localhost:5174 | Halaman React Kiosk |

✅ Jika kedua URL muncul, **setup selesai!**

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

### Permission error
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### npm install error di WSL (permission denied)
```bash
docker exec apm_rehab_vite chown -R 1000:1000 /app/node_modules
```
