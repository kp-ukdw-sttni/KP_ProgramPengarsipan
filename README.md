<div align="center">

# 📁 E-Archive STTNI

### Sistem Informasi Pengarsipan Dokumen Digital

**Sekolah Tinggi Teologi Nazarene Indonesia (STTNI)**

---

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

</div>

---

## 📖 Tentang Aplikasi

**E-Archive STTNI** adalah sistem pengarsipan dokumen digital berbasis web yang dirancang khusus untuk kebutuhan tata kelola dokumen di lingkungan **Sekolah Tinggi Teologi Nazarene Indonesia (STTNI)**. Aplikasi ini dibangun sebagai bagian dari program **Kerja Praktik (KP)** dengan tujuan menggantikan proses pengarsipan manual menjadi sistem yang terdigitalisasi, terstruktur, dan mudah diakses.

Sistem ini memungkinkan pengelolaan dokumen secara terpusat dengan kontrol akses berbasis peran (*role-based access control*), sehingga setiap pengguna hanya dapat mengakses dokumen sesuai dengan hak dan wewenangnya.

---

## ✨ Fitur Utama

| Fitur | Deskripsi |
|---|---|
| 📂 **Manajemen Dokumen** | Unggah, edit, hapus, dan lihat dokumen dengan pratinjau PDF langsung di browser |
| 🗂️ **Penjelajah Folder** | Navigasi dokumen layaknya file explorer berdasarkan struktur divisi/prodi |
| 🔐 **Kontrol Akses Berbasis Peran** | Setiap pengguna memiliki hak akses berbeda sesuai perannya |
| 📋 **Kategori Dokumen** | Pengelompokan dokumen berdasarkan kategori dan sub-kategori yang terstruktur |
| 📤 **Peminjaman & Persetujuan** | Alur pengajuan akses dokumen dengan sistem persetujuan bertingkat |
| 🔍 **Audit Trail Log** | Pencatatan setiap aktivitas pengguna secara otomatis untuk transparansi |
| 🗑️ **Recycle Bin** | Dokumen yang dihapus disimpan sementara dan dapat dipulihkan |
| 👥 **Manajemen Pengguna** | Kelola akun pengguna, penetapan peran, dan status akun |
| 📊 **Dashboard Ringkasan** | Tampilan statistik dan aktivitas terkini secara real-time |

---

## 👥 Peran Pengguna (Role)

| Peran | Hak Akses |
|---|---|
| **Superadmin** | Akses penuh ke seluruh fitur dan manajemen sistem |
| **Operator** | Kelola dokumen, kategori, persetujuan akses, dan audit log |
| **Staf TU** | Unggah & kelola dokumen serta menyetujui peminjaman |
| **Kaprodi** | Melihat dokumen dan menyetujui peminjaman |
| **Dekan** | Melihat dokumen dan menyetujui peminjaman |
| **Dosen** | Melihat & mengajukan peminjaman dokumen |
| **Karyawan** | Melihat & mengajukan peminjaman dokumen |
| **Mahasiswa** | Melihat & mengajukan peminjaman dokumen yang diizinkan |

---

## 🛠️ Teknologi yang Digunakan

### Backend
- **[Laravel 11](https://laravel.com)** — PHP Framework
- **[Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)** — Role & Permission Management
- **[Spatie Laravel Backup](https://spatie.be/docs/laravel-backup)** — Database & File Backup
- **[Tighten Ziggy](https://github.com/tighten/ziggy)** — Laravel Routes untuk JavaScript

### Frontend
- **[Vue.js 3](https://vuejs.org)** — Reactive UI Framework
- **[Inertia.js](https://inertiajs.com)** — SPA tanpa API terpisah
- **[Tailwind CSS 3](https://tailwindcss.com)** — Utility-first CSS Framework
- **[Vite](https://vitejs.dev)** — Build tool modern
- **[vue-pdf-embed](https://github.com/hrynko/vue-pdf-embed)** — Pratinjau dokumen PDF

---

## ⚙️ Cara Instalasi

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js >= 18.x & NPM
- Database MySQL/MariaDB

### Langkah Instalasi

**1. Clone repositori**
```bash
git clone https://github.com/kp-ukdw-sttni/KP_ProgramPengarsipan.git
cd KP_ProgramPengarsipan
```

**2. Install dependensi PHP**
```bash
composer install
```

**3. Install dependensi Node.js**
```bash
npm install
```

**4. Konfigurasi environment**
```bash
cp .env.example .env
php artisan key:generate
```

**5. Sesuaikan konfigurasi database di file `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username
DB_PASSWORD=password
```

**6. Jalankan migrasi dan seeder**
```bash
php artisan migrate --seed
```

**7. Buat symbolic link untuk storage**
```bash
php artisan storage:link
```

**8. Build aset frontend**
```bash
npm run build
```

**9. Jalankan server**
```bash
php artisan serve
```

Akses aplikasi di **http://localhost:8000**

---

## 🔑 Akun Demo (Setelah Seeder)

| Email | Password | Peran |
|---|---|---|
| `admin@sttni.ac.id` | `admin123` | Superadmin |
| `operator_akademik@sttni.ac.id` | `operator123` | Operator |
| `stafftu@sttni.ac.id` | `staff123` | Staf TU |
| `kaprodi.ti@sttni.ac.id` | `kaprodi123` | Kaprodi |
| `dekan.ftis@sttni.ac.id` | `dekan123` | Dekan |
| `dosen.ti@sttni.ac.id` | `dosen123` | Dosen |
| `mahasiswa@sttni.ac.id` | `mahasiswa123` | Mahasiswa |

---

## 📁 Struktur Direktori Penting

```
KP_ProgramPengarsipan/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controller untuk setiap fitur
│   │   └── Middleware/         # Middleware termasuk Inertia & auth
│   ├── Models/                 # Eloquent Models (Arsip, Divisi, dll)
│   └── Services/               # Business logic layer
├── database/
│   ├── migrations/             # Skema database
│   └── seeders/                # Data awal (role, user, dll)
├── resources/
│   └── js/
│       ├── Components/         # Komponen Vue reusable
│       ├── Composables/        # Vue composable (useAuth, useRoute)
│       ├── Layouts/            # Layout utama aplikasi
│       └── Pages/              # Halaman Vue per fitur
├── routes/
│   ├── web.php                 # Definisi route web
│   └── auth.php                # Route autentikasi
└── storage/
    └── app/public/             # Penyimpanan file dokumen
```

---

## 👨‍💻 Pengembang

Proyek ini dikembangkan sebagai bagian dari **Kerja Praktik (KP)** di:

- **Institusi:** Universitas Kristen Duta Wacana (UKDW)
- **Tempat KP:** Sekolah Tinggi Teologi Nazarene Indonesia (STTNI)

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).
