# Perpustakaan Digital SDN Pengampon 3 Cirebon (PustakaAI)
### Project Mata Kuliah Rekayasa Perangkat Lunak
**Program Studi S1 Teknik Informatika 

[![Laravel CI/CD Pipeline](https://github.com/faidilaqiel/perpustakaan-sdn-pengampon3/actions/workflows/ci.yml/badge.svg)](https://github.com/faidilaqiel/perpustakaan-sdn-pengampon3/actions)
![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=flat&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)

---

## 👤 Identitas Mahasiswa 
* **Nama Lengkap** : **Faidil Aqiel**
* **NIM** : **41257189**
* **Kelas** : **Reguler Sore / 1F-2025**
* **Program Studi** : Teknik Informatika

---

## 📖 Deskripsi Aplikasi & Fitur Unggulan
Aplikasi **Perpustakaan Digital SDN Pengampon 3** adalah sistem informasi manajemen perpustakaan sekolah dasar berbasis web *full-stack* yang mentransformasikan pencatatan manual menjadi serba otomatis dan cerdas:

1. **Multi-Role Authentication**: Akses khusus untuk **Petugas Perpustakaan (Faidil Aqiel)**, **Siswa**, **Guru**, dan **Kepala Sekolah (H. Noni Risanto, M.Pd.I.)**.
2. **Katalog & Peminjaman Mandiri**: Siswa dapat mencari koleksi buku dan mengajukan pinjam mandiri (maksimal 2 buku, durasi 7 hari).
3. **Sirkulasi Real-Time & Scan QR Code**: Petugas dapat menyetujui peminjaman, memproses pengembalian, dan memindai Kartu Anggota berbasis QR Code menggunakan kamera laptop/HP.
4. **Kalkulasi & Pembebasan Denda (Waiver)**: Perhitungan denda otomatis (Rp 1.000/hari) saat terlambat serta fitur pembebasan denda berdasar kebijakan petugas.
5. **Kunjungan Digital (Buku Tamu)**: Pencatatan otomatis kehadiran siswa/guru saat scan kartu anggota.
6. **Eksekutif Dashboard & Laporan PDF**: Kepala Sekolah dapat memantau grafik tren literasi dan mencetak laporan resmi format PDF.

### 🤖 Fitur Kecerdasan Artifisial (AI Utama)
* **PustakaAI (Virtual Librarian RAG Engine)**: Asisten cerdas interaktif 24/7 yang diinjeksikan pengetahuan (*dynamic context retrieval*) dari database katalog buku riil, ketersediaan stok, dan tata tertib SDN Pengampon 3 dengan gaya bahasa ramah anak.
* **Automated WhatsApp Reminder Gateway (Fonnte API)**: Pengingat otomatis H-1 jatuh tempo dan peringatan keterlambatan peminjaman ke nomor WhatsApp anggota/wali murid secara instan.

---

## 🎥 Tautan Video Presentasi Progres & Live Demo (Wajib OBE)
Sesuai ketentuan rubrik penilaian, video direkam dengan **webcam aktif di sudut layar** untuk verifikasi autentisitas pengerjaan mandiri:

* 📹 **Video Progres 1: PRD, Inisiasi & Konsep Aplikasi (3–5 Menit)**  
  Tautan: [Tonton Video Progres 1 di YouTube / Google Drive](https://youtu.be/placeholder-progres1) *(Silakan isi tautan video Anda)*
* 📹 **Video Progres 2: Pemodelan UML, SDD & UI/UX Figma (5–7 Menit)**  
  Tautan: [Tonton Video Progres 2 di YouTube / Google Drive](https://youtu.be/placeholder-progres2) *(Silakan isi tautan video Anda)*
* 📹 **Video Final: Live Demo Aplikasi Utuh & Verifikasi CI/CD (8–12 Menit)**  
  Tautan: [Tonton Video Final Live Demo di YouTube / Google Drive](https://youtu.be/placeholder-final) *(Silakan isi tautan video Anda)*

---

## 📑 Berkas Dokumen Resmi (Format PDF & Cetak)
* 📄 **Dokumen PRD / SRS Mandiri (IEEE 830 / ISO 29148)**: [cetakprd.html](cetakprd.html) *(Buka di browser lalu Ctrl+P untuk simpan ke PDF)*
* 📄 **Dokumen SDD & Pemodelan UML (IEEE 1016)**: [cetaksdd.html](cetaksdd.html) *(Buka di browser lalu Ctrl+P untuk simpan ke PDF)*

---

## ⚙️ Petunjuk Instalasi & Menjalankan Aplikasi (Setup Instructions)

### 1. Kebutuhan Sistem (Prerequisites)
* PHP >= 8.2 (dengan ekstensi `pdo_mysql`, `gd`, `mbstring`, `openssl`)
* Composer >= 2.x
* MySQL Database (XAMPP / Laragon)
* Node.js & NPM

### 2. Langkah Instalasi
```bash
# 1. Clone repositori
git clone https://github.com/faidilaqiel/perpustakaan-sdn-pengampon3.git
cd perpustakaan-sdn-pengampon3

# 2. Install dependensi PHP dan JavaScript
composer install
npm install

# 3. Salin berkas lingkungan .env
cp .env.example .env

# 4. Generate Application Key
php artisan key:generate

# 5. Konfigurasi Database di file .env
# DB_CONNECTION=mysql
# DB_DATABASE=perpustakaan_sdn_pengampon3
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Migrasi database dan seeder data awal
php artisan migrate --seed

# 7. Build aset frontend Tailwind
npm run build
```

### 3. Menjalankan Server Lokal (Multi-Device Testing)
Untuk demonstrasi live di laptop dan HP secara bersamaan tanpa tabrakan sesi, jalankan PHP Built-in Server dengan **multi-worker**:

```powershell
# Di Windows PowerShell:
$env:PHP_CLI_SERVER_WORKERS=4
php artisan serve --host=0.0.0.0 --port=8000
```
*(Atau di CMD biasa: `set PHP_CLI_SERVER_WORKERS=4 && php artisan serve --host=0.0.0.0 --port=8000`)*

### 4. Menjalankan Pengujian Otomatis (Unit & Feature Testing)
```bash
php artisan test
```
Alur pengujian juga otomatis dijalankan setiap `git push` melalui **GitHub Actions CI/CD Pipeline** (`.github/workflows/ci.yml`).
