# ObatKu — Product Requirements Document
**Versi:** 2.0.0 | **Tim:** Kelompok 8, 2024C | **Status:** Siap Presentasi UAS

| Field | Detail |
|---|---|
| Nama Aplikasi | ObatKu — Platform Manajemen Obat & Literasi Kesehatan Keluarga Indonesia |
| Versi | 2.0.0 — Final (SDG 3 + SDG 12 + PWA) |
| Mata Kuliah | Pemrograman Web Lanjut & Pemrograman API |
| Stack | Laravel 10, MySQL 8.0, Blade + Tailwind CSS, REST API, JWT |
| Tema SDGs | SDG 3 (Good Health & Well-Being) + SDG 12 (Responsible Consumption & Production) |
| Deploy | Railway.app |
| Tagline | *"Medisafe dibuat untuk satu pasien. ObatKu dibuat untuk satu keluarga Indonesia."* |

---

## 1. Product Overview

ObatKu adalah platform web manajemen obat dan literasi kesehatan yang dirancang khusus untuk konteks keluarga Indonesia. Dibangun dengan Laravel 10, platform ini memungkinkan **satu akun mengelola data obat, jadwal konsumsi, riwayat kesehatan, dan literasi obat untuk seluruh anggota keluarga** — dari pengguna tunggal hingga keluarga besar multi-generasi.

Versi 2.0.0 memperluas cakupan SDGs dengan dua pilar: **SDG 3** (Good Health & Well-Being) dan **SDG 12** (Responsible Consumption & Production), didukung fitur **Progressive Web App (PWA)** agar dapat diakses layaknya aplikasi mobile tanpa perlu instalasi.

### Keunggulan Kompetitif

- Satu-satunya platform lokal yang mendeteksi **interaksi obat lintas anggota keluarga** secara real-time via Open FDA API
- **Kartu Literasi Obat** dalam bahasa Indonesia sederhana — setara SMP, bukan bahasa medis
- **Manajemen multi-anggota** dalam satu akun — sesuai budaya keluarga Indonesia
- Mode **aksesibilitas font besar** untuk lansia
- Loop lengkap: alert stok habis → cari apotek terdekat via Google Maps
- **Modul EcoMed**: notifikasi kadaluarsa, panduan disposal aman, laporan jejak limbah obat — mendukung SDG 12
- **PWA** : bisa diinstall di layar utama HP, tersedia offline, push notification meski browser ditutup

### Konteks Pengembangan

- Memenuhi UAS Pemrograman Web Lanjut: Laravel, Auth, Authorization 2 role, Blade Components, Repository Pattern
- Memenuhi UAS Pemrograman API: REST API, JWT, Basic Auth, API Key, External API, Postman, Dokumentasi
- Tema SDG 3: meningkatkan literasi dan kepatuhan konsumsi obat keluarga Indonesia
- Tema SDG 12: mengurangi pemborosan dan pencemaran lingkungan dari limbah obat rumah tangga

---

## 2. Target Users

| Segmen | Profil | Kebutuhan Utama |
|---|---|---|
| Admin | Pengelola platform, dosen penguji | Dashboard statistik, monitoring interaksi, export data, statistik EcoMed |
| User — Personal | Individu yang mengelola obat sendiri | Profil diri, CRUD obat pribadi, jadwal & konfirmasi konsumsi |
| User — Families | Kepala keluarga yang merawat anggota | Multi-member, alert interaksi lintas anggota, laporan limbah obat keluarga |

### Karakteristik Pengguna Utama

- Usia 25–60 tahun: kepala keluarga yang merawat lansia atau pasien kronis
- Pasien kronis (hipertensi, diabetes, jantung) yang konsumsi 3+ obat/hari
- Lansia 60+ tahun: dibantu anggota keluarga yang lebih muda
- Akses via browser desktop/mobile — tidak perlu install aplikasi (atau cukup install sebagai PWA)

### Data Survei Pendahuluan (n=28, Nov 2024)

- **72%** pernah lupa minum obat minimal sekali seminggu
- **68%** tidak tahu efek samping obat yang rutin dikonsumsi
- **81%** tidak punya sistem pencatatan obat keluarga yang terstruktur
- **94%** tertarik menggunakan platform manajemen obat digital berbahasa Indonesia
- **76%** mengaku membuang obat sisa ke tempat sampah biasa atau saluran air *(mendorong penambahan SDG 12)*

---

## 3. Main Problem

### Masalah 1 — Kepatuhan Konsumsi Obat Sangat Rendah
> 50% pasien penyakit kronis di negara berkembang tidak meminum obat dengan benar (WHO, 2003). Tidak ada sistem reminder, jadwal, dan konfirmasi yang terintegrasi dan mudah digunakan oleh keluarga Indonesia.

### Masalah 2 — Literasi Obat Indonesia Sangat Rendah
> Hanya 49% masyarakat Indonesia memiliki literasi kesehatan yang cukup (Kemenkes, 2021). Informasi obat tersedia dalam bahasa medis Inggris yang tidak dipahami pengguna awam. Tidak ada platform yang menerjemahkan info Open FDA ke bahasa Indonesia sederhana.

### Masalah 3 — Risiko Interaksi Obat pada Lansia Tidak Terdeteksi
> Lansia Indonesia rata-rata konsumsi 5–8 jenis obat/hari (polifarmasi, BPOM 2022). Tidak ada tools lokal yang mendeteksi interaksi berbahaya antar obat — terlebih lintas anggota keluarga.

### Masalah 4 — Limbah Obat Rumah Tangga Mencemari Lingkungan *(SDG 12)*
> Diperkirakan 30–40% obat yang dibeli rumah tangga Indonesia tidak habis dan dibuang sembarangan. Pembuangan ke selokan menyebabkan kontaminasi antibiotik dan hormon pada tanah dan air. Belum ada panduan disposal obat yang mudah diakses masyarakat awam.

### Gap Kompetitor

| Fitur | Medisafe / MyTherapy | ObatKu v2.0.0 |
|---|---|---|
| Bahasa antarmuka | Inggris | Indonesia (bahasa awam) |
| Target pengguna | Satu pasien | Satu keluarga multi-anggota |
| Deteksi interaksi lintas anggota | ✗ | ✓ via Open FDA API |
| Kartu literasi bahasa Indonesia | ✗ | ✓ |
| Data obat BPOM-aware | ✗ | ✓ |
| Apotek terdekat saat stok habis | ✗ | ✓ via Google Maps |
| Notifikasi kadaluarsa obat | ✗ | ✓ Modul EcoMed |
| Panduan disposal obat aman | ✗ | ✓ Modul EcoMed |
| Laporan jejak limbah obat | ✗ | ✓ Modul EcoMed |
| PWA / install tanpa App Store | ✗ | ✓ |

---

## 4. Core Features

### F-01 — Autentikasi & Otorisasi (2 Role)
- Halaman login: dua tombol role paralel — **Admin** (biru gelap) dan **User** (hijau)
- Session management via Laravel Sanctum
- Middleware role-based: redirect otomatis jika akses route yang bukan haknya
- Logout menghapus session dan invalidate JWT token

### F-02 — Profil Personal
- User pilih mode **Personal** setelah login
- Form data diri: nama, usia, kondisi medis, nama dokter, kontak darurat
- Dashboard personal: profil lengkap, daftar obat aktif, jadwal hari ini, tombol Literasi Obat

### F-03 — Manajemen Keluarga Multi-Member
- User pilih mode **Families** setelah login
- Tambah anggota: nama, usia, hubungan keluarga, kondisi medis (tidak ada batas jumlah)
- Dashboard keluarga: card per anggota — status kesehatan + interaksi + kadaluarsa obat
- Klik card → detail anggota: CRUD obat, jadwal, riwayat, literasi, status kadaluarsa

### F-04 — Manajemen Obat & Jadwal
- CRUD obat: nama, nama generik (auto-fill Open FDA), dosis, stok, ambang alert, **expiry_date** (wajib)
- Relasi polymorphic: obat bisa milik Personal atau FamilyMember
- Jadwal minum: waktu, frekuensi (harian/mingguan), hari spesifik
- Konfirmasi konsumsi: tombol **Sudah Minum** atau **Lewati** per jadwal
- Histori konsumsi dengan filter tanggal & per anggota

### F-05 — Deteksi Interaksi Obat Lintas Anggota *(Fitur Unggulan)*
- Saat obat baru ditambahkan → otomatis query **Open FDA Drug Interaction API**
- Memeriksa kombinasi obat baru dengan **semua** obat seluruh anggota keluarga
- Alert merah di dashboard + deskripsi risiko jika interaksi terdeteksi
- Notifikasi WhatsApp ke pemilik akun via **Twilio API**

### F-06 — Kartu Literasi Obat
- Tiap obat punya halaman info edukatif dari Open FDA
- Konten diterjemahkan ke bahasa Indonesia setara SMP
- 4 kategori dengan warna berbeda:
  - 🔵 **Biru** — Kegunaan
  - 🟡 **Kuning** — Efek Samping
  - 🔴 **Merah** — Larangan
  - 🟣 **Ungu** — Interaksi Obat
- Toggle font besar untuk aksesibilitas lansia

### F-07 — Alert Stok + Apotek Terdekat
- Stok berkurang otomatis setiap konfirmasi konsumsi
- Alert dashboard + notifikasi WhatsApp via Twilio saat stok mencapai ambang batas
- Tombol langsung ke halaman cari apotek (**Google Maps Places API**)
- Tampilan apotek: jarak, jam buka, rating, tombol navigasi

### F-08 — Dashboard Kepatuhan & Laporan
- Grafik kepatuhan mingguan/bulanan per anggota (bar chart biru-hijau)
- Statistik: % tepat waktu, streak terbaik, total terlewat
- Admin: statistik global platform
- Export laporan kepatuhan CSV/PDF

### F-09 — Modul EcoMed — Manajemen Limbah Obat *(BARU | SDG 12)*

**Tujuan:** Mengurangi pemborosan dan pencemaran lingkungan dari limbah obat rumah tangga, mendukung SDG 12 secara terintegrasi dalam alur penggunaan sehari-hari.

#### F-09a — Pelacakan Tanggal Kadaluarsa
- Indikator warna pada kartu obat:
  - 🟢 Hijau: > 90 hari
  - 🟡 Kuning: 30–90 hari — *"Segera digunakan"*
  - 🔴 Merah: < 30 hari — *"Hampir kadaluarsa"*
  - ⚪ Abu-abu: sudah melewati tanggal kadaluarsa
- Filter di `/medicines`: tampilkan obat kadaluarsa dalam 30/60/90 hari

#### F-09b — Sistem Notifikasi Kadaluarsa Bertahap
- Scheduler Laravel jalan tiap hari pukul **08.00 WIB**
- Logika notifikasi:
  - **H-90**: push notifikasi ringan di dashboard
  - **H-30**: WhatsApp Twilio — *"Segera habiskan atau kembalikan ke apotek"*
  - **H-7**: WhatsApp urgent + link panduan disposal
  - **H+0**: banner merah di dashboard, obat ditandai *"Harus dibuang"*
- Log tersimpan di tabel `expiry_notification_logs` — mencegah notifikasi duplikat

#### F-09c — Panduan Disposal Obat Aman
- Halaman `/ecomed/disposal` — bisa diakses dari dashboard dan notifikasi
- Panduan bahasa Indonesia per kategori obat:
  - **Tablet/kapsul**: hancurkan + campur bahan tidak layak makan + wadah tertutup
  - **Cair/sirup**: jangan tuang ke selokan, campur bahan pengikat, kemas rapat
  - **Salep/tetes**: kosongkan tube/botol, tutup rapat sebelum dibuang
  - **Antibiotik/obat keras**: kembalikan ke apotek atau Puskesmas terdekat
- Sumber: **BPOM RI** + **WHO Safe Disposal of Unwanted Medicines**

#### F-09d — Laporan Jejak Limbah Obat
- Widget di dashboard: *"Estimasi obat terselamatkan bulan ini"*
- Metrik: obat habis sebelum kadaluarsa, obat dibuang dengan panduan, **medication waste rate** keluarga
- Admin: statistik global platform — total obat kadaluarsa terhindar
- Export CSV per keluarga via `/ecomed/report`

#### F-09e — Integrasi dengan Fitur Existing
- Alert stok (F-07): jika stok habis DAN kadaluarsa dekat → sarankan tidak beli lebih
- Form CRUD obat (F-04): `expiry_date` wajib, validasi server + client-side
- Card anggota (F-03): badge *"X obat hampir kadaluarsa"*
- Detail obat: tab baru **"Status Lingkungan"** — sisa hari & panduan disposal

---

## 5. User Flow

### Alur Admin
1. `/login` → klik **Admin** → masukkan email & password
2. Dashboard admin (dark navy topbar) → lihat statistik platform
3. Kelola pengguna: daftar, monitor kepatuhan per keluarga
4. Monitoring alert interaksi obat
5. Pantau statistik EcoMed global: total obat kadaluarsa terhindar, medication waste rate
6. Export laporan CSV/PDF

### Alur User — Mode Personal
1. Login → klik **User** → halaman Index User
2. Pilih mode **Personal** → isi/edit Form Data Diri
3. Tambah obat baru: nama + `expiry_date` → Open FDA auto-fill nama generik
4. Obat tersimpan → konfigurasi jadwal → konfirmasi konsumsi harian
5. Stok berkurang → alert → klik **Cari Apotek** → navigasi ke apotek
6. Obat mendekati kadaluarsa → notifikasi → klik **Panduan Disposal** → ikuti langkah EcoMed

### Alur User — Mode Families
1. Login → klik **User** → halaman Index User
2. Pilih mode **Families** → Index Families (daftar anggota)
3. Tambah anggota → isi Form Data Anggota → tambah obat dengan `expiry_date`
4. Dashboard Keluarga: card per anggota — status aman / interaksi / kadaluarsa
5. Klik card → detail: CRUD obat, jadwal, histori, literasi, status kadaluarsa
6. Tambah obat → deteksi interaksi otomatis + cek kadaluarsa
7. Obat mendekati kadaluarsa → notifikasi WhatsApp → link panduan disposal

---

## 6. Pages / Screens

| # | Route | Isi Konten | Akses |
|---|---|---|---|
| 1 | `/login` | Form email + password + 2 tombol role | Public |
| 2 | `/admin/dashboard` | Statistik platform, daftar pengguna, alert interaksi global, statistik EcoMed, export | Admin |
| 3 | `/dashboard` | Stat cards, pilih mode (Personal/Families), jadwal hari ini, grafik kepatuhan, widget EcoMed | User |
| 4 | `/personal` | Form profil + Dashboard Personal (obat aktif, jadwal, alert stok, alert kadaluarsa) | User |
| 5 | `/personal/edit` | Form edit data diri | User |
| 6 | `/families` | Index daftar anggota + card (status kesehatan + kadaluarsa) + tombol tambah | User |
| 7 | `/families/members/create` | Form tambah/edit anggota keluarga | User |
| 8 | `/families/members/{id}` | Detail anggota: obat, jadwal, histori, literasi, kadaluarsa | User |
| 9 | `/medicines` | Daftar semua obat + form tambah + filter kadaluarsa | User |
| 10 | `/medicines/{id}` | Detail obat: info, stok, jadwal, riwayat 7 hari, konfirmasi, tab Status Lingkungan | User |
| 11 | `/medicines/{id}/literasi` | Kartu Literasi Obat: 4 kategori, toggle font besar | User |
| 12 | `/schedules` | Kalender jadwal + konfirmasi harian | User |
| 13 | `/consumptions/history` | Riwayat konsumsi filter tanggal & anggota | User |
| 14 | `/apotek` | Cari apotek terdekat via Google Maps Places API | User |
| 15 | `/profile/settings` | Edit password, pengaturan notifikasi, toggle aksesibilitas lansia | User |
| 16 | `/ecomed/disposal` | Panduan disposal obat aman per kategori — **SDG 12** | User |
| 17 | `/ecomed/report` | Laporan jejak limbah obat: medication waste rate, export CSV — **SDG 12** | User |
| 18 | `/offline` | Halaman fallback offline **PWA** | Public |

---

## 7. UI Style

Desain terinspirasi **Nuvica Medical Website** — light mode, clean, profesional, dominasi biru medis, aksen hijau kesehatan. Modul EcoMed menggunakan aksen hijau lebih dalam sebagai identitas visual tersendiri.

### Color Palette

| Warna | Hex | Peran | Penggunaan |
|---|---|---|---|
| Biru Primary | `#185FA5` | Warna utama brand | Tombol utama, navbar, link aktif, heading aksen |
| Biru Dark | `#042C53` | Heading utama | H1, judul kartu, dark header admin |
| Biru Medium | `#378ADD` | Grafik | Bar chart kepatuhan, progress bar |
| Biru Light | `#F8FAFF` | Background halaman | Page bg, hero section |
| Hijau EcoMed | `#1D9E75` | Aksi positif, EcoMed identity | Tombol Families, pill Aman, semua elemen EcoMed |
| Hijau Light | `#E1F5EE` | Background EcoMed | Kartu literasi kegunaan, modul EcoMed bg |
| Merah Alert | `#E24B4A` | Bahaya, kritis | Alert interaksi, kadaluarsa < 30 hari |
| Kuning Warning | `#EF9F27` | Peringatan sedang | Kadaluarsa 30–90 hari, efek samping card |
| Ungu Literasi | `#7F77DD` | Info interaksi | Kartu interaksi obat di halaman literasi |

### Tipografi

- Font utama: **Inter** / Tailwind default
- H1: 28–32px, font-weight 500, warna `#042C53`
- Subheading: 13–14px, font-weight 500, warna `#185FA5`
- Body: 13px, warna `#5F5E5A`, line-height 1.6
- Aksesibilitas lansia: toggle menaikkan semua font +4px
- EcoMed badge & heading: `#1D9E75`

---

## 8. Technical Requirements

### Stack Teknologi

| Layer | Teknologi | Detail |
|---|---|---|
| Backend | Laravel 10 (PHP 8.2) | MVC, Eloquent ORM, Migrations, Seeders, Repository Pattern, Scheduled Commands |
| Frontend | Blade + Tailwind CSS | Blade Components reusable, responsif mobile-first |
| Database | MySQL 8.0 | 11 tabel, relasi Eloquent, polymorphic untuk obat |
| Web Auth | Laravel Sanctum | Session-based, 2 role: admin & user |
| API Auth | tymon/jwt-auth | JWT Bearer Token untuk semua endpoint REST API |
| Basic Auth | Custom middleware | Endpoint proxy `/api/drugs/search` |
| API Key | Tabel api_keys + middleware | Header `X-API-KEY` untuk stock alerts & EcoMed report |
| External API 1 | **Open FDA** (api.fda.gov) | Auto-fill nama generik, kartu literasi, deteksi interaksi; response di-cache ke DB |
| External API 2 | **Google Maps Places API** | Cari apotek terdekat saat stok hampir habis |
| External API 3 | **Twilio API** (WA/SMS) | Reminder harian, alert stok, alert interaksi, alert kadaluarsa EcoMed |
| External API 4 | **Web Push (VAPID)** | Push notification PWA meski tab browser ditutup |
| Scheduler | Laravel Task Scheduling | `php artisan schedule:run` — cron harian cek kadaluarsa + notifikasi EcoMed |
| Testing API | Postman | 10 collection, env variables: `{{base_url}}`, `{{token}}`, `{{api_key}}` |
| Dokumentasi API | Postman Docs + Swagger | Public docs link + OpenAPI spec via L5-Swagger |
| Deploy | Railway.app | CI/CD dari GitHub, MySQL Railway, auto-deploy on push |

### Skema Database (11 Tabel)

| Tabel | Kolom Utama | Relasi |
|---|---|---|
| `users` | id, name, email, password, role(admin\|user), api_key | HasOne PersonalProfile, HasMany Families |
| `personal_profiles` | id, user_id(FK), age, medical_conditions, doctor_name, emergency_contact | BelongsTo User, HasMany Medicines (polymorphic) |
| `families` | id, user_id(FK), family_name | BelongsTo User, HasMany FamilyMembers |
| `family_members` | id, family_id(FK), name, age, relation, medical_conditions | BelongsTo Family, HasMany Medicines (polymorphic) |
| `medicines` | id, owner_id(FK), owner_type, name, generic_name, dosage, stock_qty, stock_alert_at, fda_drug_id, **expiry_date** | MorphTo Personal/Member, HasMany Schedules |
| `schedules` | id, medicine_id(FK), time, frequency, days_of_week(JSON), is_active | BelongsTo Medicine, HasMany Consumptions |
| `consumptions` | id, schedule_id(FK), taken_at, status(taken\|skipped), note | BelongsTo Schedule |
| `api_keys` | id, user_id(FK), key(unique), scope, rate_limit, expires_at | BelongsTo User |
| `expiry_notification_logs` *(EcoMed)* | id, medicine_id(FK), notif_type(h90\|h30\|h7\|expired), sent_at, channel(wa\|dashboard), status | BelongsTo Medicine |
| `push_subscriptions` *(PWA)* | id, user_id(FK), endpoint, public_key, auth_token, device_type | BelongsTo User |
| `offline_sync_queue` *(PWA)* | id, user_id(FK), action_type, payload(JSON), synced_at | BelongsTo User |

### REST API — 25 Endpoint

| # | Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|---|
| 1 | POST | `/api/auth/register` | Public | Register akun baru |
| 2 | POST | `/api/auth/login` | Public | Login & dapatkan JWT token |
| 3 | GET | `/api/auth/me` | JWT | Data user yang sedang login |
| 4 | POST | `/api/auth/logout` | JWT | Logout & invalidate token |
| 5 | GET | `/api/personal` | JWT | Ambil profil personal user |
| 6 | PUT | `/api/personal` | JWT | Update profil personal |
| 7 | GET | `/api/families` | JWT | List keluarga milik user |
| 8 | POST | `/api/families/members` | JWT | Tambah anggota keluarga baru |
| 9 | PUT | `/api/families/members/{id}` | JWT | Update data anggota keluarga |
| 10 | GET | `/api/medicines` | JWT | List obat (personal + anggota) |
| 11 | POST | `/api/medicines` | JWT | Tambah obat baru (wajib expiry_date) |
| 12 | PUT | `/api/medicines/{id}` | JWT | Update data obat |
| 13 | DELETE | `/api/medicines/{id}` | JWT | Hapus data obat |
| 14 | GET | `/api/schedules` | JWT | List jadwal minum obat |
| 15 | POST | `/api/consumptions` | JWT | Catat konfirmasi minum obat |
| 16 | GET | `/api/consumptions/history` | JWT | Histori konsumsi per anggota |
| 17 | GET | `/api/stock/alerts` | JWT + API Key | Daftar obat stok hampir habis |
| 18 | GET | `/api/drugs/search` | Basic Auth | Proxy pencarian obat ke Open FDA |
| 19 | GET | `/api/admin/stats` | JWT + Admin | Statistik platform (admin only) |
| 20 ★ | GET | `/api/ecomed/expiry-alerts` | JWT + API Key | Daftar obat mendekati kadaluarsa — SDG 12 |
| 21 ★ | GET | `/api/ecomed/disposal-guide/{type}` | JWT | Panduan disposal per kategori (tablet/cair/luar/keras) |
| 22 ★ | GET | `/api/ecomed/report` | JWT + API Key | Laporan medication waste rate, export CSV |
| 23 ★ | POST | `/api/pwa/subscribe` | JWT | Simpan push subscription endpoint — PWA |
| 24 ★ | DELETE | `/api/pwa/unsubscribe` | JWT | Hapus push subscription — PWA |
| 25 ★ | POST | `/api/pwa/sync` | JWT | Background Sync aksi offline — PWA |

> ★ = Endpoint baru v2.0.0

---

## 9. Progressive Web App (PWA)

ObatKu diimplementasikan sebagai PWA — website yang berfungsi layaknya aplikasi mobile native tanpa perlu diunggah ke App Store atau Google Play.

### Web App Manifest (`manifest.json`)

```json
{
  "name": "ObatKu — Manajemen Obat Keluarga",
  "short_name": "ObatKu",
  "start_url": "/dashboard",
  "display": "standalone",
  "theme_color": "#185FA5",
  "background_color": "#F8FAFF",
  "orientation": "portrait",
  "icons": [
    { "src": "/icons/icon-72.png", "sizes": "72x72", "purpose": "maskable" },
    { "src": "/icons/icon-192.png", "sizes": "192x192", "purpose": "maskable" },
    { "src": "/icons/icon-256.png", "sizes": "256x256", "purpose": "any" },
    { "src": "/icons/icon-512.png", "sizes": "512x512", "purpose": "maskable" }
  ]
}
```

### Service Worker — 5 Kapabilitas

| Kapabilitas | Implementasi di ObatKu |
|---|---|
| **Precache** | Simpan aset statis saat install: `/dashboard`, `/medicines`, `/ecomed/disposal`, CSS Tailwind, ikon |
| **Runtime Caching** | Open FDA & Google Maps: Cache First 72 jam — data tetap tersedia offline |
| **Background Sync** | Konfirmasi konsumsi offline → simpan ke IndexedDB → kirim ke server saat online |
| **Push Notification** | VAPID keys — notifikasi jadwal, stok, kadaluarsa, interaksi meski tab ditutup |
| **Offline Fallback** | Halaman `/offline` jika konten belum di-cache |

### Halaman Tersedia Offline

| Halaman | Offline? | Catatan |
|---|---|---|
| `/dashboard` | ✓ | Data kepatuhan & jadwal dari cache terakhir |
| `/medicines` | ✓ | Daftar obat terakhir termasuk status kadaluarsa |
| `/medicines/{id}/literasi` | ✓ | Cache 72 jam per obat dari Open FDA |
| `/ecomed/disposal` | ✓ | Halaman statis — selalu tersedia, penting saat di Puskesmas |
| Konfirmasi konsumsi | ✓ via Background Sync | Tersimpan lokal, dikirim saat online |
| `/apotek` | ✗ | Butuh GPS + Google Maps real-time |
| `/ecomed/report` | ✗ | Butuh kalkulasi server terbaru |

### Push Notification — Dua Jalur

| Jenis | Trigger | Non-PWA | PWA (Tambahan) |
|---|---|---|---|
| Jadwal minum obat | Scheduler 08.00 WIB | Twilio WhatsApp/SMS | Web Push — banner di layar HP |
| Alert stok habis | Stok ≤ ambang | Twilio WhatsApp/SMS | Web Push + badge di ikon app |
| Alert kadaluarsa EcoMed | H-90/H-30/H-7/H+0 | Twilio + dashboard | Web Push dengan action button: *Panduan Disposal* |
| Interaksi obat | Saat obat ditambahkan | Alert merah dashboard | Web Push urgent + badge merah |

### Implementasi Laravel untuk PWA

- Route `GET /sw.js` — serve Service Worker dengan header `Content-Type: application/javascript`
- Route `GET /offline` — halaman fallback offline
- `layouts/app.blade.php` — tambah `<link rel="manifest">`, `<meta theme-color>`, script registrasi SW
- VAPID Keys: generate via `php artisan webpush:vapid`, simpan di `.env`
- Model `User` HasMany `PushSubscriptions`
- Job `PushNotificationJob` — dispatch dari Scheduler dan dari observer obat

### Target Lighthouse PWA

| Kriteria | Target |
|---|---|
| Installable (manifest + SW aktif) | Pass |
| PWA Optimized (HTTPS, viewport, splash) | Pass |
| Performance (FCP) | ≥ 85 |
| Accessibility | ≥ 90 |
| Best Practices | ≥ 90 |
| SEO | ≥ 90 |

### Relevansi PWA terhadap SDGs

| SDG | Kontribusi PWA |
|---|---|
| **SDG 3** | Notifikasi push jadwal obat bekerja meski browser ditutup → meningkatkan kepatuhan tanpa perlu buka website aktif |
| **SDG 10** | Tanpa instalasi App Store/Play Store → ramah pengguna HP entry-level, storage terbatas, koneksi lambat |
| **SDG 12** | Panduan disposal `/ecomed/disposal` tersedia offline → bisa dibaca di apotek/Puskesmas tanpa koneksi |

---

## 10. Constraints

### Teknis
- Aplikasi berbasis web — tidak ada versi native iOS/Android
- OAuth2 tidak diimplementasikan (opsional, di luar scope UAS)
- Open FDA: rate limit gratis ~240 req/menit — semua response di-cache ke DB lokal
- Twilio: mode trial membatasi penerima WA ke nomor terverifikasi — cukup untuk demo
- Google Maps Places API memerlukan API Key Google Cloud (berbayar setelah kuota gratis)
- Semua external API dipanggil dari backend Laravel — API key tidak terekspos ke browser
- Railway.app tier gratis: sleep 30 menit tidak aktif; Laravel Scheduler perlu cron eksternal

### Desain
- Light mode only — dark mode tidak disupport
- Bahasa Indonesia only — tidak ada multi-bahasa
- Layout minimal lebar 768px (tablet landscape) untuk tampilan optimal
- Mode aksesibilitas hanya menaikkan ukuran font, tidak mengubah layout

### Data
- Tidak ada integrasi rekam medis rumah sakit
- Tidak ada fitur e-commerce / pembelian obat online
- Tidak ada fitur telemedicine atau konsultasi dokter
- Data Open FDA bersumber dari FDA AS — beberapa nama generik mungkin beda dengan BPOM Indonesia
- Panduan disposal EcoMed mengacu BPOM RI & WHO, tidak terintegrasi real-time dengan sistem BPOM
- Medication waste rate bersifat estimasi berbasis data konsumsi — bukan pengukuran berat fisik

---

## 11. Success Metrics

### Metrik SDG 3 (Beta Testing 8 Keluarga, 2 Minggu)

| Metrik | Baseline | Target | Hasil Beta | Cara Ukur |
|---|---|---|---|---|
| Kepatuhan minum obat | 58% | > 80% | **84% ✓** | % konfirmasi / total jadwal |
| Pengetahuan efek samping | 32% | > 60% | **67% ✓** | Survei pasca-pakai |
| Punya jadwal terstruktur | 22% | 100% | **100% ✓** | Verifikasi data schedules di DB |
| Alert stok aktif & terpantau | 18% | > 90% | **100% ✓** | Log consumptions + alert |
| Interaksi obat terdeteksi | 0 kasus | ≥ 1 | **3 kasus ✓** | Log alert interaksi admin |
| Kepuasan pengguna | — | > 80% | **94% ✓** | Survei rating 1–5 |

### Metrik SDG 12 — EcoMed (Target Pasca-Rilis)

| Metrik | Baseline | Target | Cara Ukur |
|---|---|---|---|
| Obat kadaluarsa terdeteksi sebelum dibuang | 0% | > 70% | % medicines dengan notifikasi terkonfirmasi |
| Pengguna tahu cara disposal benar | 24% | > 70% | Survei pasca-pakai EcoMed |
| Medication waste rate per keluarga | ~35% | < 15% | Rasio obat terbuang vs dikonsumsi habis |
| Notifikasi kadaluarsa terkirim tepat waktu | — | > 95% | Log `expiry_notification_logs` |
| Halaman disposal dikunjungi | — | > 60% pengguna aktif | Analytics log akses |

### Checklist Teknis UAS

| Syarat | Status |
|---|---|
| Laravel 10 + MVC + Blade Components | ✅ Terpenuhi |
| Authentication (login/register/logout) | ✅ Terpenuhi |
| Authorization 2 role (Admin & User) | ✅ Terpenuhi |
| Repository Pattern (Interface + Implementation) | ✅ Terpenuhi — 6 repo class |
| Migrasi & relasi antar tabel | ✅ Terpenuhi — 11 migrasi, polymorphic |
| REST API + JWT + Basic Auth + API Key | ✅ Terpenuhi — 25 endpoint |
| External API (diambil dari luar) | ✅ Terpenuhi — Open FDA, Google Maps, Twilio, VAPID |
| Custom API (dibuat sendiri) | ✅ Terpenuhi — 25 endpoint REST API |
| Testing API dengan Postman | ✅ Terpenuhi — 10 collection |
| Dokumentasi API | ✅ Terpenuhi — Postman Docs + Swagger |
| Tema SDGs + inovasi TIK | ✅ Terpenuhi — SDG 3 + SDG 12 + PWA |
| Dampak dibuktikan data | ✅ Terpenuhi — survei n=28 + beta 8 keluarga |
| Produk bisa dioperasikan | ✅ Terpenuhi — deploy live Railway.app |

---

## Appendix — Referensi Target SDG 12

| Target | Implementasi di ObatKu |
|---|---|
| **12.3** — Kurangi pemborosan produk per kapita | Notifikasi kadaluarsa bertahap (F-09a & F-09b) mencegah obat terbuang sebelum habis digunakan |
| **12.4** — Pengelolaan limbah kimia yang bertanggung jawab | Panduan disposal aman (F-09c) sesuai pedoman BPOM RI dan WHO |
| **12.5** — Kurangi limbah melalui pencegahan & pengurangan | Laporan medication waste rate (F-09d) mendorong perilaku konsumsi obat yang terukur |
| **12.8** — Informasi relevan untuk gaya hidup berkelanjutan | Panduan disposal terintegrasi dalam notifikasi WhatsApp dan halaman `/ecomed/disposal` yang tersedia offline |

*Sumber: UN SDGs Goal 12, BPOM RI Pedoman Disposal Obat Rumah Tangga, WHO Guidelines for Safe Disposal of Unwanted Pharmaceuticals.*

---

*ObatKu v2.0.0 — Kelompok 8, 2024C*
