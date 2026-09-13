<div align="center">

<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="230" alt="Laravel">

# 📚 Polinema Library

**Sistem Informasi Perpustakaan berbasis web dengan 3 level pengguna — Admin, Petugas, dan Anggota.**
Mulai dari kelola koleksi buku, transaksi peminjaman & pengembalian, perhitungan denda otomatis, sampai cetak laporan bulanan dalam format PDF.

<br>

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![AdminLTE](https://img.shields.io/badge/AdminLTE-3.16-00A65A?style=for-the-badge&logo=adminlte&logoColor=white)](https://adminlte.io)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-4.6-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![CI](https://img.shields.io/github/actions/workflow/status/Almasyriqi/Laravel_Web_Perpustakaan/ci.yml?branch=master&style=for-the-badge&logo=githubactions&logoColor=white&label=CI)](https://github.com/Almasyriqi/Laravel_Web_Perpustakaan/actions/workflows/ci.yml)
[![License](https://img.shields.io/badge/License-MIT-22272e?style=for-the-badge)](https://github.com/laravel/laravel/blob/master/LICENSE)

<br>

[**✨ Fitur**](#-fitur-utama) &nbsp;•&nbsp;
[**🧱 Teknologi**](#-teknologi-yang-digunakan) &nbsp;•&nbsp;
[**🗂️ Database**](#️-struktur-database) &nbsp;•&nbsp;
[**🔄 Alur**](#-alur-peminjaman) &nbsp;•&nbsp;
[**⚙️ Instalasi**](#️-instalasi) &nbsp;•&nbsp;
[**🧪 Testing**](#-testing) &nbsp;•&nbsp;
[**🖼️ Screenshot**](#️-screenshot-aplikasi) &nbsp;•&nbsp;
[**🗺️ Roadmap**](#️-roadmap--saran-pengembangan)

</div>

---

## 📖 Tentang Proyek

**Polinema Library** adalah aplikasi perpustakaan digital yang dibangun dengan **Laravel 13** dan panel admin **AdminLTE 3**. Aplikasi ini dirancang untuk menggantikan pencatatan peminjaman buku yang masih manual, dengan alur kerja yang jelas antara petugas dan anggota perpustakaan.

<table>
<tr>
<td width="33%" align="center">

### 🔐
**Multi-Role Auth**

Tiga level akses dengan middleware terpisah, registrasi mandiri + verifikasi email

</td>
<td width="33%" align="center">

### 🔄
**Transaksi Lengkap**

Pinjam → konfirmasi → perpanjang → kembali, dengan denda otomatis

</td>
<td width="33%" align="center">

### 📊
**Laporan PDF & Excel**

Rekap peminjaman per bulan atau rentang tanggal bebas, cetak PDF (DomPDF) atau export Excel

</td>
</tr>
</table>

---

## ✨ Fitur Utama

### 🌐 Umum (semua pengguna)

| | Fitur | Keterangan |
|:--:|---|---|
| 🏠 | **Landing page** | Halaman publik dengan tombol login & register |
| 📝 | **Registrasi anggota** | Pendaftaran mandiri, otomatis berperan sebagai *anggota* |
| 📧 | **Verifikasi email** | Wajib verifikasi sebelum bisa mengakses dashboard |
| 🔑 | **Login fleksibel** | Bisa masuk dengan **username** maupun **email** |
| 👤 | **Profil & ganti password** | Setiap pengguna dapat memperbarui datanya sendiri |
| 🔔 | **Notifikasi interaktif** | Alert & konfirmasi menggunakan SweetAlert |
| 📧 | **Email pengingat** | Anggota dikirimi email H-1 sebelum jatuh tempo dan saat terlambat (scheduler harian + queue) |
| 🌓 | **Dark mode & mobile** | Tombol dark mode di navbar (preferensi tersimpan di session), tabel melipat kolom di layar sempit |

### 👑 Admin

| | Fitur | Keterangan |
|:--:|---|---|
| 📊 | **Dashboard statistik** | Tren peminjaman 12 bulan, buku terpopuler, daftar keterlambatan dengan estimasi denda |
| 🧑‍💼 | **CRUD Admin** | Kelola akun administrator + pencarian data |
| 🧑‍🏫 | **CRUD Petugas** | Kelola akun petugas perpustakaan + pencarian data |
| 🎓 | **CRUD Anggota** | Kelola data anggota (NIM, jurusan, kontak, alamat) |
| 🏷️ | **CRUD Kategori** | Pengelompokan koleksi buku |
| 📚 | **CRUD Buku** | Judul, penulis, penerbit, stok, dan sampul buku (disimpan di Storage disk) |
| 🗄️ | **Arsip & pulihkan** | Buku/anggota yang dihapus masuk arsip (soft delete), riwayat tetap utuh, bisa dipulihkan |
| 🔁 | **CRUD Peminjaman** | Kontrol penuh atas seluruh transaksi peminjaman |
| 🧾 | **Cetak & export laporan** | Laporan per bulan atau rentang tanggal bebas — PDF dan Excel (.xlsx) |

### 🧑‍🏫 Petugas

| | Fitur | Keterangan |
|:--:|---|---|
| 📊 | **Dashboard statistik** | Tren peminjaman, buku terpopuler, daftar keterlambatan siap diproses |
| 🎓 | **CRUD Anggota** | Pendataan anggota perpustakaan |
| 🏷️ | **CRUD Kategori & Buku** | Pengelolaan koleksi perpustakaan, termasuk arsip & pulihkan |
| ✅ | **Konfirmasi peminjaman** | Menyetujui pengajuan pinjam dari anggota (stok berkurang, jatuh tempo ditetapkan) |
| ➕ | **Peminjaman langsung** | Input transaksi untuk anggota yang datang ke loket |
| ⏳ | **Perpanjangan** | Memperpanjang masa pinjam (maksimal 1×) |
| 📥 | **Pengembalian** | Hitung lama pinjam & denda otomatis, stok buku dikembalikan |
| 🧾 | **Cetak & export laporan** | Laporan per bulan atau rentang tanggal bebas — PDF dan Excel (.xlsx) |

### 🎓 Anggota

| | Fitur | Keterangan |
|:--:|---|---|
| 📖 | **Katalog buku** | Grid kartu bersampul dengan pencarian (judul/penulis/penerbit), filter kategori, opsi "hanya yang tersedia", dan paginasi |
| 🛒 | **Ajukan peminjaman** | Pinjam buku langsung dari katalog (status awal: `konfirmasi`) |
| 📋 | **Riwayat peminjaman** | Memantau status, tanggal jatuh tempo (badge *Terlambat*), dan denda tiap transaksi |
| ⏳ | **Ajukan perpanjangan** | Memperpanjang masa pinjam buku yang sedang dipinjam |
| ❌ | **Batalkan pengajuan** | Selama status masih `konfirmasi`, peminjaman bisa dibatalkan |
| 📅 | **Kalender & aturan** | Dashboard berisi kalender dan ringkasan aturan peminjaman |

---

## 🧱 Teknologi yang Digunakan

| Kategori | Teknologi | Versi |
|---|---|---|
| **Framework** | [Laravel](https://laravel.com) | `13.x` |
| **Bahasa** | PHP | `^8.3` |
| **Database** | MySQL / MariaDB | — |
| **UI Panel** | [AdminLTE 3](https://github.com/jeroennoten/Laravel-AdminLTE) — `jeroennoten/laravel-adminlte` | `3.16` |
| **Frontend** | Bootstrap, jQuery — bundel AdminLTE (`public/vendor`) + plugin CDN, tanpa build step | `4.6` / `3.6` |
| **Autentikasi** | [Laravel UI](https://github.com/laravel/ui) — scaffolding + email verification | `4.6` |
| **Cetak PDF** | [DomPDF](https://github.com/barryvdh/laravel-dompdf) — `barryvdh/laravel-dompdf` | `3.1` |
| **Export Excel** | [Laravel Excel](https://github.com/SpartnerNL/Laravel-Excel) — `maatwebsite/excel` (PhpSpreadsheet) | `4.0` |
| **Notifikasi** | [SweetAlert](https://github.com/realrashid/sweet-alert) — `realrashid/sweet-alert` | `7.3` |
| **Kalender** | FullCalendar | — |
| **Testing** | PHPUnit (SQLite in-memory), Faker | `12.5` |
| **Dev Tools** | Laravel Debugbar, IDE Helper, Laravel Pint | — |

---

## 🗂️ Struktur Database

```mermaid
erDiagram
    USERS ||--o| ADMIN : "profil"
    USERS ||--o| PETUGAS : "profil"
    USERS ||--o| ANGGOTA : "profil"
    KATEGORI ||--o{ BUKU : "mengelompokkan"
    ANGGOTA ||--o{ PEMINJAMAN : "melakukan"
    BUKU ||--o{ PEMINJAMAN : "dipinjam pada"

    USERS {
        bigint id PK
        string username UK
        string name
        string email UK
        string password
        string role "admin, petugas, anggota"
        timestamp email_verified_at
        timestamp deleted_at
    }
    ADMIN {
        bigint id PK
        bigint user_id FK
        string no_hp
        text alamat
    }
    PETUGAS {
        bigint id PK
        bigint user_id FK
        date tgl_lahir
        string no_hp
        text alamat
    }
    ANGGOTA {
        bigint nim PK
        bigint user_id FK
        string jurusan
        date tgl_lahir
        string no_hp
        text alamat
        timestamp deleted_at
    }
    KATEGORI {
        bigint id PK
        string nama
        text keterangan
    }
    BUKU {
        bigint id PK
        bigint kategori_id FK
        string judul
        string penulis
        string penerbit
        text keterangan
        int stok
        string gambar
        timestamp deleted_at
    }
    PEMINJAMAN {
        bigint id PK
        bigint anggota_id FK
        bigint buku_id FK
        int jumlah
        date tgl_pinjam
        date tgl_harus_kembali
        date tgl_kembali
        int lama_pinjam
        string status "index"
        tinyint perpanjang
        int denda
        timestamp pengingat_dikirim_at
        timestamp teguran_dikirim_at
        timestamp created_at
        timestamp updated_at
    }
```

---

## 🔄 Alur Peminjaman

```mermaid
flowchart LR
    A([🎓 Anggota pilih buku]) --> B[status: konfirmasi]
    B -->|❌ dibatalkan anggota| X([Peminjaman dihapus])
    B -->|✅ dikonfirmasi petugas| C[status: dipinjam<br/>stok berkurang<br/>jatuh tempo +7 hari]
    C -->|⏳ perpanjang 1x| D[status: perpanjang<br/>jatuh tempo +14 hari]
    C --> E([📥 Pengembalian])
    D --> E
    E --> F[status: kembali<br/>stok bertambah<br/>denda dihitung]
```

### 📜 Aturan peminjaman

| Aturan | Ketentuan |
|---|---|
| ⏱️ Masa pinjam | **7 hari** |
| 🔁 Perpanjangan | **maksimal 1×** — total menjadi 14 hari |
| 💸 Denda keterlambatan | **Rp 2.000 / hari** untuk setiap judul buku |
| 🤝 Pembayaran denda | Dibayarkan langsung ke petugas saat pengembalian |
| 🧾 Konfirmasi | Pengajuan pinjam dari anggota harus dikonfirmasi petugas |

> ⚙️ Masa pinjam, batas perpanjangan, dan tarif denda dibaca dari [`config/perpustakaan.php`](config/perpustakaan.php) dan bisa ditimpa lewat `.env` (`PERPUS_MASA_PINJAM`, `PERPUS_MASA_PERPANJANG`, `PERPUS_MAKS_PERPANJANG`, `PERPUS_DENDA_PER_HARI`, `PERPUS_PENGINGAT_HARI_SEBELUM`). Dashboard anggota menampilkan nilai yang sama.

---

## ⚙️ Instalasi

### 📋 Prasyarat

- **PHP** `>= 8.3` (proyek ini dikembangkan di atas [Laragon](https://laragon.org))
- **Composer** `2.x`
- **MySQL / MariaDB**
- *(opsional)* Akun SMTP untuk mengirim email verifikasi sungguhan — secara default email ditulis ke `storage/logs/laravel.log`

### 🚀 Langkah-langkah

**1️⃣ Clone repository**

```bash
git clone https://github.com/Almasyriqi/Laravel_Web_Perpustakaan.git
cd Laravel_Web_Perpustakaan
```

**2️⃣ Install dependency**

```bash
composer install
```

**3️⃣ Siapkan file environment**

```bash
cp .env.example .env
php artisan key:generate
```

**4️⃣ Buat database baru** di phpMyAdmin (misalnya `perpustakaan`), lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=perpustakaan     # sesuaikan nama database Anda
DB_USERNAME=root             # sesuaikan username database
DB_PASSWORD=                 # sesuaikan password database
```

**5️⃣ Atur email verifikasi.** Secara default `MAIL_MAILER=log`, sehingga tautan verifikasi akun baru bisa disalin dari `storage/logs/laravel.log` tanpa perlu SMTP. Untuk mengirim email sungguhan, ganti ke SMTP:

<details>
<summary>📮 <b>Konfigurasi Mailtrap</b> — direkomendasikan untuk development</summary>

<br>

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_SCHEME=smtp                 # STARTTLS otomatis di port 587; gunakan smtps untuk port 465
MAIL_FROM_ADDRESS=no-reply@polinemalibrary.test
MAIL_FROM_NAME="${APP_NAME}"
```

Panduan lengkap: [Laravel Kirim Email dengan Mailtrap](https://ilmucoding.com/laravel-kirim-email-mailtrap/)

</details>

<details>
<summary>✉️ <b>Konfigurasi Gmail SMTP</b></summary>

<br>

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=app_password_gmail    # gunakan App Password, bukan password akun
MAIL_SCHEME=smtp                 # STARTTLS otomatis di port 587; gunakan smtps untuk port 465
MAIL_FROM_ADDRESS=email_anda@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

> ⚠️ Gunakan **App Password** dari akun Google yang sudah mengaktifkan 2FA, dan **jangan pernah commit file `.env`** ke repository.

</details>

**6️⃣ Migrasi & isi data awal**

```bash
php artisan migrate --seed
```

**7️⃣ Hubungkan folder sampul buku** — unggahan disimpan di `storage/app/public/sampul` dan dilayani lewat `public/storage`:

```bash
php artisan storage:link
```

> `APP_URL` di `.env` harus sesuai alamat yang Anda buka di browser (mis. `http://127.0.0.1:8000`), karena URL sampul dibangun dari nilai itu.

**8️⃣ Jalankan test** *(opsional, tidak butuh database — memakai SQLite in-memory)*

```bash
php artisan test
```

**9️⃣ Jalankan aplikasi** 🎉

```bash
php artisan serve
```

Buka browser ke **<http://127.0.0.1:8000>** — selamat mencoba! 😉

---

## 🔑 Akun Default (hasil seeder)

| Role | Username | Password | Hak akses |
|:--:|---|---|---|
| 👑 **Admin** | `admin` | `12345678` | Akses penuh ke seluruh modul |
| 🧑‍🏫 **Petugas** | `petugas` | `12345678` | Transaksi & pengelolaan koleksi |
| 🎓 **Anggota** | `almasyriqi` | `12345678` | Katalog & peminjaman buku |

> 💡 Seeder juga membuat **20 user + data anggota** dan **50 buku** dummy melalui Faker, sehingga aplikasi langsung berisi data untuk diuji coba.
>
> 🔒 Akun di atas hanya untuk lingkungan development — **ganti seluruh kredensial sebelum digunakan secara nyata.**

---

## ⏰ Penjadwalan & Queue (email pengingat)

Setiap pagi pukul **07:00** command `perpus:kirim-pengingat` mengirim dua jenis email ke anggota (dijadwalkan di [`routes/console.php`](routes/console.php)):

| Email | Kapan | Isi |
|---|---|---|
| 📨 **Pengingat jatuh tempo** | H-1 sebelum `tgl_harus_kembali` (ubah lewat `PERPUS_PENGINGAT_HARI_SEBELUM`) | Judul buku, tanggal harus kembali, tarif denda |
| ⚠️ **Pemberitahuan terlambat** | Sekali, saat peminjaman melewati jatuh tempo | Hari keterlambatan & estimasi denda berjalan |

Setiap peminjaman hanya dikirimi **sekali** per jenis email (penanda kolom `pengingat_dikirim_at` / `teguran_dikirim_at`), jadi command aman dijalankan berulang.

```bash
php artisan perpus:kirim-pengingat --dry-run   # lihat berapa email yang akan dikirim
php artisan perpus:kirim-pengingat             # kirim sekarang (email ke storage/logs/laravel.log bila MAIL_MAILER=log)
php artisan schedule:list                      # cek jadwal
php artisan schedule:work                      # jalankan scheduler di development
```

Di server, tambahkan satu entri cron: `* * * * * cd /path/ke/aplikasi && php artisan schedule:run >> /dev/null 2>&1`.

> 📬 Notifikasi diantrekan (`ShouldQueue`). Dengan `QUEUE_CONNECTION=sync` (default) email dikirim langsung; untuk produksi ganti ke `database` dan jalankan `php artisan queue:work` — tabel `jobs` sudah disediakan migrasi.

---

## 🖼️ Screenshot Aplikasi

<details open>
<summary><b>🌐 Halaman Umum</b></summary>

<br>

| Landing Page | Login |
|:--:|:--:|
| <img src="screenshot/awal.PNG" width="430"> | <img src="screenshot/login.PNG" width="430"> |
| Halaman publik sebelum masuk | Login dengan **username atau email** |

| Registrasi | Verifikasi Email |
|:--:|:--:|
| <img src="screenshot/register.PNG" width="430"> | <img src="screenshot/verifRegis.PNG" width="430"> |
| Pendaftaran anggota baru | Wajib verifikasi sebelum dapat mengakses dashboard |

</details>

<details>
<summary><b>👑 Dashboard Admin</b></summary>

<br>

| Home Admin | Profil |
|:--:|:--:|
| <img src="screenshot/homeAdmin.PNG" width="430"> | <img src="screenshot/profileAdmin.PNG" width="430"> |

| Ganti Password | Data Admin |
|:--:|:--:|
| <img src="screenshot/pwAdmin.PNG" width="430"> | <img src="screenshot/crudAdmin.PNG" width="430"> |

| Data Petugas | Data Anggota |
|:--:|:--:|
| <img src="screenshot/crudPetugas.PNG" width="430"> | <img src="screenshot/crudAnggota.PNG" width="430"> |

| Kategori Buku | Data Buku |
|:--:|:--:|
| <img src="screenshot/crudKategori.PNG" width="430"> | <img src="screenshot/crudBuku.PNG" width="430"> |

| Detail Buku | Data Peminjaman |
|:--:|:--:|
| <img src="screenshot/crudBuku1.PNG" width="430"> | <img src="screenshot/crudPeminjaman.PNG" width="430"> |

| Cetak Laporan Bulanan | |
|:--:|:--|
| <img src="screenshot/laporanAdmin.PNG" width="430"> | Laporan peminjaman difilter per bulan, lalu dicetak sebagai PDF |

</details>

<details>
<summary><b>🧑‍🏫 Dashboard Petugas</b></summary>

<br>

| Home Petugas | Profil |
|:--:|:--:|
| <img src="screenshot/homePetugas.PNG" width="430"> | <img src="screenshot/profilePetugas.PNG" width="430"> |

| Ganti Password | Data Anggota |
|:--:|:--:|
| <img src="screenshot/pwPetugas.PNG" width="430"> | <img src="screenshot/crudAnggotaP.PNG" width="430"> |

| Kategori Buku | Data Buku |
|:--:|:--:|
| <img src="screenshot/crudKategoriP.PNG" width="430"> | <img src="screenshot/crudBukuP.PNG" width="430"> |

| Detail Buku | Transaksi Peminjaman |
|:--:|:--:|
| <img src="screenshot/crudBukuP1.PNG" width="430"> | <img src="screenshot/peminjamanPetugas.PNG" width="430"> |
| | Petugas dapat memperpanjang & menerima pengembalian buku |

| Konfirmasi Peminjaman | Cetak Laporan |
|:--:|:--:|
| <img src="screenshot/konfPetugas.PNG" width="430"> | <img src="screenshot/laporanPetugas.PNG" width="430"> |
| Menyetujui pengajuan pinjam dari anggota | Rekap peminjaman bulanan dalam PDF |

</details>

<details>
<summary><b>🎓 Dashboard Anggota</b></summary>

<br>

| Home Anggota | Profil |
|:--:|:--:|
| <img src="screenshot/homeAnggota.PNG" width="430"> | <img src="screenshot/profileAnggota.PNG" width="430"> |
| Berisi kalender dan aturan peminjaman | Anggota dapat memperbarui datanya sendiri |

| Ganti Password | Katalog Buku |
|:--:|:--:|
| <img src="screenshot/pwAnggota.PNG" width="430"> | <img src="screenshot/bukuAnggota1.PNG" width="430"> |

| Detail Buku | Form Peminjaman |
|:--:|:--:|
| <img src="screenshot/bukuAnggota2.PNG" width="430"> | <img src="screenshot/pinjamAnggota.PNG" width="430"> |

| Riwayat Peminjaman | |
|:--:|:--|
| <img src="screenshot/hasilPinjamAnggota.PNG" width="430"> | Peminjaman dapat dibatalkan selama status masih `konfirmasi` |

</details>

---

## 🧪 Testing

Test berjalan di **SQLite in-memory** (lihat `phpunit.xml`), jadi tidak menyentuh database MySQL Anda.

```bash
php artisan test                 # seluruh suite
php artisan test --filter=Peminjaman   # hanya alur peminjaman
vendor/bin/pint --dirty          # rapikan format file yang berubah
```

> 🤖 Setiap push dan pull request otomatis menjalankan `vendor/bin/pint --test` + `php artisan test` lewat GitHub Actions ([`.github/workflows/ci.yml`](.github/workflows/ci.yml)).

| Suite | Cakupan |
|---|---|
| `SmokeTest` | Halaman utama tiap role dapat dirender, middleware role menolak akses silang, toggle dark mode bertahan antar halaman |
| `AuthTest` | Login via username/email, logout `POST`, verifikasi email, registrasi |
| `PeminjamanFlowTest` | Ajukan → konfirmasi → perpanjang → kembali, stok, denda, pembatalan, edit admin |
| `LaporanTest` | Filter bulan + tahun dan rentang tanggal bebas, validasi periode, keluaran PDF & Excel |
| `ValidationTest` | Duplikat username/email, profil orang lain, jumlah/status tidak valid |
| `EagerLoadingTest` | Halaman daftar memakai ≤ 6 query untuk 15 baris (tidak ada N+1) |
| `SoftDeleteTest` | Arsip & pulihkan buku/anggota, akun terarsip tidak bisa login, riwayat utuh |
| `SampulBukuTest` | Unggah/ganti sampul di Storage disk, path legacy tetap dilayani |
| `RoleTest` | Enum `Role`, helper `isAdmin()`, middleware `role:admin,petugas` |
| `PeminjamanServiceTest` | Unit test perhitungan denda dan lama pinjam dengan aturan yang dapat diatur |
| `KatalogTest` | Pencarian judul/penulis/penerbit, filter kategori & ketersediaan, paginasi katalog anggota |
| `StatistikTest` | Buku terpopuler, tren 12 bulan, daftar keterlambatan & estimasi denda, tampilan dashboard |
| `PengingatJatuhTempoTest` | Pengingat H-N & teguran terkirim sekali, `--dry-run`, isi email, notifikasi masuk antrean |

---

## 🗺️ Roadmap & Saran Pengembangan

Beberapa hal yang layak dikerjakan berikutnya, diurutkan berdasarkan prioritas.

### 🔴 Prioritas Tinggi — keamanan & fondasi

- [x] **Upgrade ke Laravel 13 + PHP 8.3** — target dinaikkan dari 11 ke 13 karena Laravel 11 sudah *end-of-life* sejak Maret 2026; struktur aplikasi mengikuti skeleton ramping Laravel 11+
- [x] **Logout hanya lewat route `POST`** dengan CSRF token; verifikasi email diaktifkan kembali
- [x] **Filter laporan per bulan dan tahun** (`whereMonth` + `whereYear`, pemilih tahun di halaman laporan)
- [x] **Mutasi stok dalam DB transaction + `lockForUpdate`** lewat `App\Services\PeminjamanService` — sekaligus memperbaiki stok yang bergeser saat admin mengedit dan anggota yang bisa membatalkan peminjaman orang lain
- [x] **Validasi lewat Form Request** — rule `unique:users` kini benar-benar dieksekusi, edit meng-*ignore* data sendiri
- [x] **Automated test PHPUnit** — 87 test (auth, akses per role, alur pinjam → konfirmasi → perpanjang → kembali → denda, laporan, validasi, arsip, sampul); jalankan dengan `php artisan test`

### 🟡 Prioritas Menengah — kualitas kode

- [x] **Eloquent relationship + eager loading** menggantikan *raw join* — relasi `Peminjaman::anggota()`/`Anggota::peminjaman()` diperbaiki (FK `anggota_id` → `nim`), halaman daftar terukur 2–4 query
- [x] **Logika denda & lama pinjam di `PeminjamanService`**, tarif & masa pinjam dari `config/perpustakaan.php` / env `PERPUS_*`
- [x] **Sampul buku lewat Storage disk** (`storage:link`), file lama dihapus saat diganti; sampul legacy di `public/images` tetap dilayani
- [x] **Kolom `tgl_harus_kembali`, `timestamps`, index** `status`, `tgl_pinjam`, `(anggota_id, status)` — denda dihitung dari jatuh tempo, riwayat menampilkan badge *Terlambat*
- [x] **Soft delete buku, anggota, dan akun user** + halaman Arsip & Pulihkan; hapus ditolak bila masih ada pinjaman aktif
- [x] **Enum `Role` + satu middleware `role:admin,petugas`** menggantikan tiga middleware; dipilih alih-alih `spatie/laravel-permission` karena hanya ada tiga role tetap yang terikat tabel profil

### 🟢 Nice to Have — fitur baru

- [x] 🔍 **Pencarian & filter katalog** anggota — scope `Buku::cari()/dariKategori()/tersedia()`, `KatalogRequest`, grid kartu 12 per halaman dengan paginasi Bootstrap 4 yang mempertahankan query string
- [x] 📧 **Notifikasi email jatuh tempo** — command `perpus:kirim-pengingat` (H-1 + teguran terlambat, sekali per peminjaman lewat kolom penanda) dijadwalkan harian di `routes/console.php`; notifikasi `ShouldQueue`, tabel `jobs` disediakan untuk `QUEUE_CONNECTION=database`; lihat bagian [Penjadwalan & Queue](#-penjadwalan--queue-email-pengingat)
- [x] 📊 **Dashboard statistik** admin & petugas — `StatistikService`: ringkasan (sedang dipinjam, menunggu konfirmasi, terlambat, denda bulan ini), bar chart tren 12 bulan (Chart.js), 5 buku terpopuler, daftar keterlambatan dengan estimasi denda; agregasi per bulan di PHP agar jalan di MySQL & SQLite
- [x] 📑 **Export laporan ke Excel** (`maatwebsite/excel` 4 — resmi mendukung Laravel 13) + **rentang tanggal bebas** (`/laporan/rentang?dari=&sampai=`, maks. 366 hari) — `PeriodeLaporan` dipakai bersama oleh HTML, PDF, dan Excel; filter memakai `whereBetween` sehingga index `tgl_pinjam` terpakai
- [ ] 🔖 **Barcode / QR code** buku dan kartu anggota untuk mempercepat transaksi loket
- [ ] ⭐ **Rating & ulasan buku** serta fitur *booking* buku yang stoknya sedang habis
- [x] 🌓 **Dark mode** lewat widget bawaan AdminLTE (`darkmode-widget`, preferensi di session) + CSS pelengkap untuk elemen custom; **mobile**: ekstensi DataTables Responsive dimuat (sebelumnya `responsive: true` tidak berefek), kartu detail fluid, tombol aksi tabel lebih ringkas
- [x] 🤖 **CI GitHub Actions** — [`.github/workflows/ci.yml`](.github/workflows/ci.yml) menjalankan `pint --test` + `php artisan test` di setiap push & PR (PHP 8.3, SQLite in-memory); seluruh kode diformat Pint sekali sebagai prasyarat
- [x] 🧹 **Bersihkan Laravel Mix** — `package.json`, `webpack.mix.js`, `resources/js|sass`, dan bundel `public/js/app.js` (3 MB) / `public/css/app.css` dihapus; semua asset berasal dari bundel AdminLTE + CDN sehingga instalasi tidak lagi butuh Node.js

---

## 👥 Tim Pengembang

| NIM | Nama | GitHub |
|---|---|---|
| `1941720057` | Muhammad Syifa'ul Ikrom Almasyriqi | [@Almasyriqi](https://github.com/Almasyriqi) |
| `1941720171` | Muhammad Fauzan | [@fauzanmuh](https://github.com/fauzanmuh) |

> 🎓 Proyek ini dikerjakan sebagai **Tugas Besar mata kuliah Pemrograman Web Lanjut** — Teknik Informatika, Politeknik Negeri Malang.

---

## 📄 Lisensi

Dirilis di bawah lisensi **MIT**, mengikuti lisensi framework [Laravel](https://github.com/laravel/laravel/blob/master/LICENSE).

<div align="center">

<br>

**Kalau proyek ini bermanfaat, jangan lupa beri ⭐ pada repository ini!**

Dibuat dengan ❤️ dan ☕ menggunakan [Laravel](https://laravel.com)

</div>
