# CRUD Buku Laravel - Deployment Guide ke Vercel

Panduan lengkap untuk mendeploy aplikasi **Laravel 12 CRUD Buku** ke Vercel dengan database **Supabase (PostgreSQL)**.

---

## Prasyarat

- [x] Akun [Vercel](https://vercel.com) (gratis)
- [x] Akun [Supabase](https://supabase.com) (gratis)
- [x] Akun [GitHub](https://github.com)
- [x] Git terinstall di komputer

---

## Langkah 1: Setup Database Supabase

1. Buka [https://supabase.com](https://supabase.com) dan login
2. Klik **"New Project"**
3. Isi nama project, password database, dan pilih region terdekat (Singapore/Southeast Asia)
4. Tunggu project selesai dibuat (~1 menit)
5. Buka **Project Settings > Database**
6. Catat informasi berikut:
   - **Host**: `db.xxxxxxxxxxxx.supabase.co`
   - **Database name**: `postgres`
   - **Port**: `5432`
   - **User**: `postgres`
   - **Password**: password yang Anda buat tadi

---

## Langkah 2: Generate APP_KEY

Jalankan perintah ini di terminal lokal:

```bash
php artisan key:generate --show
```

Simpan output-nya (format: `base64:xxxxx...`).

---

## Langkah 3: Push ke GitHub

```bash
git add .
git commit -m "feat: ready for Vercel deployment"
git push origin main
```

---

## Langkah 4: Deploy ke Vercel

1. Buka [https://vercel.com/new](https://vercel.com/new)
2. Klik **"Add New Project"**
3. Pilih repository GitHub `crud-buku-laravel`
4. Klik **"Deploy"** — Vercel akan otomatis mendeteksi konfigurasi dari `vercel.json`

---

## Langkah 5: Set Environment Variables di Vercel

Di dashboard Vercel project Anda, buka **Settings > Environment Variables** dan tambahkan variabel berikut:

| Variable | Value |
|----------|-------|
| `APP_NAME` | `CRUD Buku` |
| `APP_ENV` | `production` |
| `APP_KEY` | `base64:xxxxx` (hasil dari Langkah 2) |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://nama-project.vercel.app` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `db.xxxxxxxxxxxx.supabase.co` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres` |
| `DB_PASSWORD` | password Supabase Anda |
| `DB_SSLMODE` | `require` |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_STORE` | `array` |
| `LOG_CHANNEL` | `stderr` |
| `QUEUE_CONNECTION` | `sync` |

Setelah menambahkan semua variabel, klik **"Redeploy"**.

---

## Langkah 6: Jalankan Migrasi Database

Setelah deploy berhasil, jalankan migrasi dari terminal lokal menggunakan Vercel CLI:

```bash
# Install Vercel CLI jika belum ada
npm i -g vercel

# Login
vercel login

# Pull environment variables ke .env lokal
vercel env pull .env.vercel

# Jalankan migrasi dengan env Vercel
php artisan migrate --force --env=vercel
```

**Atau alternatif** — gunakan fitur SQL Editor di Supabase:

1. Buka Supabase Dashboard > SQL Editor
2. Buat tabel secara manual:

```sql
-- Tabel untuk tracking migrasi Laravel
CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- Tabel data buku
CREATE TABLE IF NOT EXISTS bukus (
    id BIGSERIAL PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(255) NOT NULL,
    penerbit VARCHAR(255) NOT NULL,
    tahun INTEGER NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Struktur File Vercel

```
/
├── api/
│   └── index.php          # Entry point Laravel untuk Vercel (serverless)
├── public/
│   ├── index.php          # Entry point Laravel standard
│   └── build/             # Asset Vite (setelah npm run build)
├── vercel.json            # Konfigurasi routing Vercel
└── .vercelignore          # File yang di-exclude dari upload
```

---

## Cara Kerja di Vercel

- Vercel menjalankan `api/index.php` sebagai **serverless function** PHP
- File statis (CSS, JS, images) di-serve langsung dari `public/`
- Storage Laravel diarahkan ke `/tmp` (writable di Vercel)
- Session menggunakan **cookie** (tidak butuh database/file)
- Cache menggunakan **array** (in-memory per request)

---

## Troubleshooting

### Error: `Storage path not writable`
Pastikan `api/index.php` sudah diupdate dengan kode pembuatan direktori `/tmp`.

### Error: `Database connection failed`
- Periksa kembali kredensial database di Vercel Environment Variables
- Pastikan `DB_SSLMODE=require` untuk Supabase
- Coba test koneksi dari lokal dengan env yang sama

### Error: `APP_KEY not set`
Generate ulang APP_KEY: `php artisan key:generate --show`

### Asset CSS/JS tidak muncul
Pastikan sudah menjalankan `npm run build` dan file `public/build/` ter-commit ke Git.
