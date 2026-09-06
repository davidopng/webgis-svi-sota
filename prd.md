# PRD — Antarmuka WebGIS Pemetaan Kerentanan Sosial Ekonomi Kampung Sota

**Nama sistem (kerja):** SOTA-GIS
**Versi dokumen:** 1.0 — Frontend Blueprint
**Disusun untuk:** AI Coding Assistant / developer frontend
**Sumber kebutuhan:** Naskah skripsi *Identifikasi dan Pemetaan Kerentanan Sosial Ekonomi Masyarakat Kampung Sota* (BAB I–III), David Leonardo Moento, Jurusan Sistem Informasi, Universitas Musamus Merauke.

---

## 0. Cara memakai dokumen ini

Dokumen ini adalah **blueprint antarmuka (casing)**. Bangun tampilan dan alur interaksinya dulu; logika perhitungan dan basis data menyusul.

Aturan main untuk implementor:

1. Kerjakan **hanya** yang tertulis di sini. Jangan menambah halaman, fitur, atau peran pengguna baru.
2. Semua angka skor, klasifikasi, dan warna yang tampil di layar **datang dari server sebagai nilai jadi**. Frontend tidak pernah menghitung SVI.
3. Selama tahap casing, isi layar dengan data contoh dari Bagian 16. Semua tombol harus dapat diklik dan semua state harus dapat ditampilkan, walau belum tersambung ke basis data.
4. Bahasa antarmuka: **Bahasa Indonesia**, kalimat biasa, tanpa singkatan teknis yang tidak dikenal perangkat kampung.

---

## 1. Konteks produk

Kampung Sota berada di Distrik Sota, Kabupaten Merauke, Papua Selatan, berbatasan darat langsung dengan Papua Nugini. Penduduk 1.686 jiwa (870 laki-laki, 816 perempuan) yang tersebar di 12 RT dan 3 RW. Permukiman utama terkonsentrasi di **Jalur A**, **Jalur B**, dan sepanjang **Poros Trans**.

Pendataan kerentanan saat ini berjalan manual dan berbentuk teks, sehingga pemerintah kampung tidak bisa melihat *di mana* rumah tangga rentan berada. Sistem ini mengubah data survei 91 rumah tangga menjadi peta digital interaktif: setiap rumah tangga menjadi satu titik berwarna sesuai kelas kerentanannya, dan pemerintah kampung memakainya sebagai bahan pertimbangan penentuan sasaran bantuan sosial.

**Unit analisis:** rumah tangga (kepala keluarga), bukan wilayah.
**Sistem koordinat:** WGS 84 (EPSG:4326), format desimal derajat.

### Prinsip produk yang mengikat desain

| Prinsip | Konsekuensi di antarmuka |
|---|---|
| Skor dihitung sistem, tidak pernah diketik manusia | Semua field skor tampil **read-only**, tanpa input, tanpa tombol ubah |
| Data hanya tampil di peta publik setelah diverifikasi Admin | Peta publik tidak punya toggle "tampilkan semua data" |
| Verifikasi memeriksa keaslian masukan, bukan nilai skor | Layar verifikasi menyandingkan **foto** dengan **jawaban survei**, bukan menampilkan slider skor |
| Alat dipakai kader di lapangan, sambil berdiri, di bawah matahari | Form operator mobile-first, target sentuh besar, kontras tinggi |
| Data menyangkut kemiskinan rumah tangga bertetangga | Nada visual tenang dan netral; tanpa ikon dramatis, tanpa istilah menghakimi seperti "miskin" |

---

## 2. Ruang lingkup

### Termasuk (kerjakan)

- Seluruh halaman untuk 4 peran: Pengunjung, Operator/Kader Kampung, Admin, Kepala Kampung
- Layout, navigasi, komponen, state (kosong, memuat, gagal, berhasil)
- Peta Leaflet.js dengan marker berwarna, filter, pencarian, dan pop-up detail
- Formulir survei 3 bagian, termasuk pengambilan koordinat GPS di sisi peramban dan pratinjau unggahan foto
- Tabel antrean verifikasi dan modal validasi berdampingan
- Grafik statistik dan panel ekspor laporan

### Tidak termasuk (abaikan dulu)

- Skema tabel, migration, relasi, dan seeding basis data
- Algoritma *additive scoring* dan klasifikasi *equal interval* di sisi server
- Autentikasi nyata, hashing, middleware peran (cukup rute dummy per peran)
- Pembuatan berkas PDF/Excel yang sungguhan (tombol cukup memicu state "menyiapkan berkas")
- Pencatatan log pembaruan di sisi server (halaman log tetap dibuat, isinya data contoh)

---

## 3. Tumpukan teknologi

| Lapis | Pilihan | Catatan |
|---|---|---|
| Framework | Laravel 11.21 | Blade + komponen Blade, bukan SPA |
| Bundler | Vite | Bawaan Laravel 11 |
| CSS | Tailwind CSS v3 | Token warna didefinisikan di `tailwind.config.js`, Bagian 6 |
| Peta | Leaflet.js 1.9.x | Via CDN atau npm, plus `leaflet.markercluster` bila titik menumpuk |
| Tabel | DataTables (jQuery) atau Alpine + paginasi Laravel | Pilih satu, konsisten di semua tabel |
| Grafik | Chart.js 4.x | Pie dan bar saja |
| Interaktivitas ringan | Alpine.js 3.x | Modal, dropdown, tab, toast, panel filter |
| Ikon | Lucide (SVG inline) | Satu set saja, jangan campur emoji |

Tanpa React, tanpa Vue, tanpa Inertia. Alasannya: sistem ini akan dipelihara perangkat kampung dan diuji dengan *black box testing* serta UAT, jadi seluruh tampilan harus dapat dirender server-side dan dibaca sebagai berkas Blade biasa.

---

## 4. Aktor dan hak akses

Empat aktor sesuai Use Case Diagram naskah:

| Aktor | Login | Kemampuan di antarmuka |
|---|---|---|
| **Pengunjung** | Tidak | Lihat peta sebaran, filter tingkat kerentanan, cari nama KK, lihat pop-up detail |
| **Operator / Kader Kampung** | Ya | Dashboard ringkas, input data warga baru, edit data **selama status masih Menunggu Verifikasi**, lihat daftar data miliknya |
| **Admin** | Ya | Semua kemampuan Operator, ditambah: verifikasi data, revisi data apa pun, hapus data, lihat log pembaruan. Admin adalah pengguna tertinggi |
| **Kepala Kampung** | Ya | Dashboard analitik dan cetak/unduh laporan rekapitulasi (PDF/Excel). **Tidak** melakukan input, edit, verifikasi, atau hapus |

### Matriks tombol per peran

Dipakai sebagai acuan menyembunyikan aksi. Jangan hanya menonaktifkan tombol — sembunyikan bila peran tidak berhak.

| Aksi | Pengunjung | Operator | Admin | Kepala Kampung |
|---|:--:|:--:|:--:|:--:|
| Lihat peta publik | ✅ | ✅ | ✅ | ✅ |
| Tambah data warga | — | ✅ | ✅ | — |
| Edit data status *Menunggu Verifikasi* | — | ✅ (miliknya) | ✅ (semua) | — |
| Edit data status *Terverifikasi* | — | — | ✅ | — |
| Verifikasi data | — | — | ✅ | — |
| Hapus data | — | — | ✅ | — |
| Lihat log pembaruan | — | — | ✅ | — |
| Ekspor laporan PDF/Excel | — | — | ✅ | ✅ |

---

## 5. Peta rute dan struktur berkas

### Rute

```
PUBLIK
GET  /                         → peta sebaran layar penuh
GET  /tentang                  → penjelasan singkat metode SVI (statis, 1 halaman)
GET  /login                    → form masuk

OPERATOR                                          prefix: /operator
GET  /operator                 → dashboard ringkas
GET  /operator/data            → daftar data yang ia input
GET  /operator/data/create     → formulir survei (3 bagian)
GET  /operator/data/{id}/edit  → formulir survei mode ubah

ADMIN                                             prefix: /admin
GET  /admin                    → dashboard utama
GET  /admin/verifikasi         → tabel antrean verifikasi
GET  /admin/data               → tabel seluruh data warga
GET  /admin/data/create        → formulir survei
GET  /admin/data/{id}/edit     → formulir survei mode ubah
GET  /admin/log                → tabel log pembaruan

KEPALA KAMPUNG                                    prefix: /pimpinan
GET  /pimpinan                 → dashboard analitik
GET  /pimpinan/laporan         → panel ekspor laporan
```

Setelah masuk, arahkan pengguna ke dashboard sesuai peran: `operator → /operator`, `admin → /admin`, `kepala_kampung → /pimpinan`.

### Struktur berkas Blade

```
resources/views/
├─ layouts/
│  ├─ app.blade.php            # kerangka panel internal (sidebar + topbar)
│  ├─ public.blade.php         # kerangka halaman publik (tanpa sidebar)
│  └─ auth.blade.php           # kerangka halaman masuk
├─ components/
│  ├─ stat-card.blade.php      # kartu metrik
│  ├─ badge-klasifikasi.blade.php
│  ├─ badge-status.blade.php
│  ├─ toast.blade.php
│  ├─ modal.blade.php
│  ├─ empty-state.blade.php
│  ├─ field-radio-svi.blade.php  # satu variabel SVI = satu komponen
│  └─ tabel-aksi.blade.php
├─ publik/
│  ├─ peta.blade.php
│  └─ tentang.blade.php
├─ operator/
│  ├─ dashboard.blade.php
│  ├─ index.blade.php
│  └─ form.blade.php           # dipakai create & edit
├─ admin/
│  ├─ dashboard.blade.php
│  ├─ verifikasi.blade.php
│  ├─ data.blade.php
│  └─ log.blade.php
├─ pimpinan/
│  ├─ dashboard.blade.php
│  └─ laporan.blade.php
└─ auth/login.blade.php

resources/js/
├─ app.js
├─ peta-publik.js              # inisialisasi Leaflet, filter, pencarian
├─ ambil-koordinat.js          # Geolocation API + peta pratinjau
└─ grafik.js                   # Chart.js
```

`operator/form.blade.php` dan `admin/data/create` memakai **komponen formulir yang sama**. Jangan menulis dua formulir survei.

---

## 6. Sistem desain

### 6.1 Arah visual

Alat kerja pemerintahan kampung di wilayah perbatasan, bukan produk konsumen. Antarmuka menahan diri: satu warna merek yang gelap dan tenang, permukaan netral, dan **warna hanya dipakai untuk menyampaikan tingkat kerentanan**. Merah/kuning/hijau adalah satu-satunya elemen berwarna kuat di layar, supaya peta terbaca dalam sekali lihat. Tidak ada gradien dekoratif, tidak ada bayangan tebal, tidak ada kartu bersudut besar yang seragam.

### 6.2 Token warna

```js
// tailwind.config.js — theme.extend.colors
colors: {
  ink:      '#14212B',   // teks utama, latar sidebar
  paper:    '#F4F6F5',   // latar halaman
  surface:  '#FFFFFF',   // kartu, panel, baris tabel
  line:     '#D7DEDC',   // garis pemisah, batas input
  muted:    '#5A6B72',   // teks sekunder, label
  brand:    '#175C6B',   // tombol utama, nav aktif, fokus
  'brand-dark': '#0F4653',

  rendah:      '#2C7A4B',  // marker & isian
  'rendah-bg': '#E4F2E9',  // latar lencana
  'rendah-tx': '#1B5233',  // teks lencana

  sedang:      '#E0A32E',
  'sedang-bg': '#FCF1DA',
  'sedang-tx': '#8A5E00',

  tinggi:      '#B3352C',
  'tinggi-bg': '#FBE7E4',
  'tinggi-tx': '#8E2820',
}
```

Warna status verifikasi memakai turunan yang sama: *Menunggu Verifikasi* memakai palet `sedang`, *Terverifikasi* memakai palet `rendah`. Ini disengaja supaya pengguna hanya perlu menghafal tiga warna di seluruh sistem.

### 6.3 Tipografi

Satu keluarga huruf: **Plus Jakarta Sans** (Google Fonts), bobot 400 / 500 / 700. Angka skor dan koordinat memakai `font-variant-numeric: tabular-nums` supaya kolom angka pada tabel dan pop-up sejajar.

| Peran | Ukuran / bobot | Pemakaian |
|---|---|---|
| Judul halaman | 24px / 700 | Satu per halaman |
| Judul bagian | 18px / 700 | Judul kartu, judul bagian formulir |
| Teks isi | 15px / 400 | Paragraf, isi tabel |
| Label field | 14px / 500 | Label input |
| Teks bantuan | 13px / 400, `muted` | Penjelasan di bawah input |
| Angka metrik | 32px / 700, tabular | Kartu statistik |

Panjang baris teks paragraf maksimal 70 karakter. Judul dan label ditulis huruf kapital di awal kalimat saja — jangan HURUF BESAR SEMUA.

### 6.4 Tata ruang dan bentuk

- Grid spasi kelipatan 4px. Jarak antar bagian formulir 32px, antar field 20px.
- Radius: `6px` untuk input dan tombol, `10px` untuk kartu dan modal, `9999px` hanya untuk lencana.
- Bayangan: satu tingkat saja, `0 1px 2px rgba(20,33,43,.08)`. Modal boleh `0 12px 32px rgba(20,33,43,.18)`.
- Lebar konten panel internal maksimal 1280px, formulir maksimal 720px.
- Tinggi minimum target sentuh **44px**; pada formulir operator naikkan ke **52px**.

### 6.5 Komponen dasar

| Komponen | Spesifikasi |
|---|---|
| Tombol utama | Latar `brand`, teks putih, 44px, radius 6px. Hover `brand-dark` |
| Tombol sekunder | Latar putih, batas `line`, teks `ink` |
| Tombol merusak | Teks dan batas `tinggi`, latar putih. Latar penuh `tinggi` hanya pada tombol konfirmasi di dalam modal hapus |
| Input teks | Tinggi 44px, batas `line` 1px, fokus: batas `brand` + ring 2px `brand`/25% |
| Input nonaktif | Latar `paper`, teks `muted`, kursor `not-allowed` |
| Radio kartu | Kotak penuh lebar yang dapat diklik seluruh areanya, bukan lingkaran kecil |
| Lencana | Teks 12px/500, padding 2px 10px, radius penuh, warna sesuai Bagian 7 |
| Kartu | Latar `surface`, batas `line`, radius 10px, padding 20px |

### 6.6 Fokus dan gerak

Cincin fokus keyboard harus terlihat di semua elemen interaktif, termasuk marker peta. Gerakan hanya sebagai jawaban atas aksi pengguna: modal muncul, panel filter membuka, toast masuk. Tidak ada animasi masuk saat halaman dimuat. Hormati `prefers-reduced-motion`.

---

## 7. Kamus tampilan

Tiga kamus ini dipakai berulang di semua halaman. Definisikan sekali sebagai komponen Blade.

### 7.1 Klasifikasi kerentanan

| Nilai | Total Skor SVI | Skor rata-rata | Warna | Bentuk marker | Lencana |
|---|---|---|---|---|---|
| Kerentanan Rendah | 9 – 14 | 1,00 – <1,67 | Hijau `#2C7A4B` | Lingkaran | `rendah-bg` / `rendah-tx` |
| Kerentanan Sedang | 15 – 20 | 1,67 – <2,33 | Kuning `#E0A32E` | Segitiga | `sedang-bg` / `sedang-tx` |
| Kerentanan Tinggi | 21 – 27 | 2,33 – 3,00 | Merah `#B3352C` | Belah ketupat | `tinggi-bg` / `tinggi-tx` |

Marker dibedakan **warna sekaligus bentuk**. Sekitar 8% laki-laki mengalami buta warna merah-hijau, dan peta ini akan dipakai perangkat kampung yang belum tentu bisa membedakan hijau dari kuning di layar terang. Bentuk adalah cadangan yang wajib, bukan hiasan.

### 7.2 Status verifikasi

Hanya ada **dua** status. Jangan membuat status ketiga.

| Nilai | Label tampil | Gaya lencana | Arti bagi pengguna |
|---|---|---|---|
| `menunggu_verifikasi` | Menunggu verifikasi | Palet `sedang`, batas 1px | Sudah tersimpan, belum tampil di peta publik |
| `terverifikasi` | Terverifikasi | Palet `rendah`, batas 1px | Sudah tampil di peta publik |

### 7.3 Jalur

Tiga nilai tetap: **Jalur A**, **Jalur B**, **Poros Trans**. Dipakai pada filter, kolom tabel, bar chart, dan dropdown formulir.

---

## 8. Halaman Pengunjung — Peta sebaran publik

**Rute:** `/` · **Layout:** `layouts/public` · **Tanpa login**

Peta adalah halamannya, bukan komponen di dalam halaman. Peta mengisi 100% *viewport*; seluruh kendali mengambang di atasnya.

### 8.1 Wireframe — desktop (≥1024px)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ ▌SOTA-GIS  Pemetaan Kerentanan Sosial Ekonomi Kampung Sota    Tentang  Masuk │  56px, putih 92%, blur
├───────────────────────────┬──────────────────────────────────────────────────┤
│ ┌───────────────────────┐ │                                                  │
│ │ 🔍 Cari nama kepala   │ │                    ◆                             │
│ │    keluarga…          │ │                          ●                       │
│ └───────────────────────┘ │              ▲                                   │
│   ↳ daftar saran muncul   │                     ◆       ▲                    │
│     di bawah kolom cari   │        ●                                         │
│                           │                                  ◆               │
│ ┌───────────────────────┐ │            ▲          ●                          │
│ │ Tingkat kerentanan    │ │                                                  │
│ │ ☑ ● Rendah        7   │ │      (Peta Leaflet.js — OpenStreetMap)           │
│ │ ☑ ▲ Sedang       60   │ │                                                  │
│ │ ☑ ◆ Tinggi       24   │ │                                                  │
│ ├───────────────────────┤ │                                                  │
│ │ Wilayah               │ │                                                  │
│ │ ☑ Jalur A        15   │ │                                          ┌─────┐ │
│ │ ☑ Jalur B        33   │ │                                          │  +  │ │
│ │ ☑ Poros Trans    43   │ │                                          │  −  │ │
│ ├───────────────────────┤ │                                          └─────┘ │
│ │  Atur ulang filter    │ │                                                  │
│ └───────────────────────┘ │                                          ┌─────┐ │
│                           │                                          │ ⌖   │ │ lokasi saya
│ Menampilkan 91 rumah      │                                          └─────┘ │
│ tangga terverifikasi      │                                                  │
│                           │  © OpenStreetMap · Data survei Okt 2025 · WGS 84 │
└───────────────────────────┴──────────────────────────────────────────────────┘
   panel kiri 320px, kartu putih mengambang, margin 16px dari tepi
```

### 8.2 Wireframe — mobile (<768px)

Panel kiri berubah jadi lembar bawah (*bottom sheet*) dua tingkat.

```
┌──────────────────────────┐      ┌──────────────────────────┐
│ ▌SOTA-GIS          ☰     │      │ ▌SOTA-GIS          ☰     │
│ ┌──────────────────────┐ │      │                          │
│ │ 🔍 Cari nama KK…     │ │      │        (peta)            │
│ └──────────────────────┘ │      │                          │
│                          │      ├──────────────────────────┤
│         (peta)           │  →   │ ═══ (tarik)              │
│                          │      │ Tingkat kerentanan       │
│                          │      │ ☑ ● Rendah          7    │
│                          │      │ ☑ ▲ Sedang         60    │
├──────────────────────────┤      │ ☑ ◆ Tinggi         24    │
│ ═══ (tarik ke atas)      │      │ Wilayah                  │
│ Filter · 91 rumah tangga │      │ ☑ Jalur A  ☑ B  ☑ Poros  │
└──────────────────────────┘      │        Terapkan          │
       tinggi awal 64px           └──────────────────────────┘
```

### 8.3 Komponen

**Peta**
- Pusat awal: koordinat permukiman Kampung Sota, zoom 15. Batasi `maxBounds` sekitar wilayah kampung supaya pengguna tidak tersesat ke laut Arafura.
- *Basemap* OpenStreetMap standar. Sediakan pengalih ke citra satelit (Esri World Imagery) di pojok kanan bawah — berguna karena banyak rumah tidak terpetakan di OSM.
- Marker memakai `L.divIcon` berisi SVG sesuai bentuk pada Bagian 7.1, ukuran 22px, garis tepi putih 2px agar terbaca di atas citra satelit.
- Gunakan *marker cluster* bila lebih dari 30 titik berada pada satu layar; bubble cluster diwarnai menurut kelas kerentanan yang paling banyak di dalamnya.
- Skala dan atribusi wajib tampil di kanan bawah, ditambah teks sumber data dan datum.

**Panel pencarian**
- Mencari **nama kepala keluarga** saja. Ketik ≥2 huruf → daftar saran maksimal 8 baris berisi nama KK + lencana klasifikasi + jalur.
- Memilih saran: peta *fly to* ke titik itu (zoom 18), marker berdenyut sekali, pop-up detail langsung terbuka.
- Tanpa hasil: "Nama itu belum ada di data terverifikasi. Coba potongan nama yang lain."

**Panel filter**
- Kotak centang untuk tiga tingkat kerentanan dan tiga jalur; semua tercentang secara bawaan.
- Setiap baris menampilkan jumlah titik. Perubahan filter langsung diterapkan pada peta (desktop) atau saat menekan "Terapkan" (mobile).
- Bila semua kotak dilepas: peta kosong dengan pesan mengambang "Tidak ada kategori yang dipilih. Centang minimal satu tingkat kerentanan."
- "Atur ulang filter" mengembalikan seluruh centang.

**Pop-up detail (modal saat marker diklik)**

Naskah menyebut pop-up memuat nama KK, total skor SVI, klasifikasi, dan data deskriptif pendukung berupa foto rumah, pekerjaan, dan jumlah anggota keluarga. Gunakan modal terpusat di desktop dan lembar bawah di mobile, bukan `L.popup` bawaan — supaya foto tampil layak.

```
┌────────────────────────────────────────────┐
│                                        [×] │
│ ┌────────────────────────────────────────┐ │
│ │                                        │ │
│ │        Foto rumah (16:9, object-cover) │ │
│ │                                        │ │
│ └────────────────────────────────────────┘ │
│                                            │
│  Bpk. Yohanis Kaize                        │
│  Jalur B · RT 03 / RW 01                   │
│                                            │
│  ┌──────────────────┬───────────────────┐  │
│  │ Total Skor SVI   │ Klasifikasi       │  │
│  │       20         │ ◆ Kerentanan      │  │
│  │  (skala 9–27)    │    Sedang         │  │
│  └──────────────────┴───────────────────┘  │
│                                            │
│  Pekerjaan              Petani             │
│  Jumlah anggota         4 orang            │
│  Koordinat        -8.44271, 140.86935      │
│                                            │
│  Skor dihitung dari 9 variabel SVI.        │
│  Pelajari metodenya →                      │
└────────────────────────────────────────────┘
```

Aturan isi pop-up:
- **Jangan** menampilkan rincian skor per variabel, pendapatan, atau status kepemilikan lahan di halaman publik. Itu data sensitif rumah tangga; publik cukup melihat hasil agregat per rumah tangga.
- Bila `foto_rumah` kosong, ganti area foto dengan blok `paper` berisi ikon rumah dan teks "Foto belum tersedia" — jangan menampilkan gambar rusak.
- Koordinat ditulis 5 desimal dengan huruf tabular.

**Kondisi khusus**
- *Memuat:* rangka abu-abu pada panel kiri + spinner kecil di tengah peta.
- *Data kosong:* peta tetap tampil, kartu mengambang di tengah: "Belum ada data terverifikasi untuk ditampilkan."
- *Gagal muat:* "Peta gagal dimuat. Periksa koneksi internet lalu muat ulang halaman." + tombol "Muat ulang".

---

## 9. Halaman Masuk

**Rute:** `/login` · **Layout:** `layouts/auth`

Satu kolom terpusat, lebar 400px, di atas latar `paper`. Isi: logo/nama sistem, judul "Masuk ke SOTA-GIS", kolom **Username**, kolom **Kata sandi** (dengan tombol lihat/sembunyikan), kotak "Ingat saya", tombol utama "Masuk", dan tautan "Kembali ke peta publik".

Kesalahan ditampilkan sebagai satu blok di atas formulir: "Username atau kata sandi tidak sesuai." Jangan memberi tahu mana yang salah.

---

## 10. Halaman Operator / Kader Kampung

**Rute:** `/operator`, `/operator/data`, `/operator/data/create` · **Layout:** `layouts/app`

Ini satu-satunya bagian sistem yang dipakai **sambil berdiri di halaman rumah orang**. Rancang mobile-first; tampilan desktop adalah bonus.

### 10.1 Kerangka panel internal

```
┌────────────┬─────────────────────────────────────────────────────────┐
│            │  Dashboard Operator                    🔔  Maria K.  ▾  │
│ SOTA-GIS   ├─────────────────────────────────────────────────────────┤
│            │                                                         │
│ ◉ Dashboard│   (isi halaman)                                         │
│ ▤ Data Saya│                                                         │
│ ＋ Input   │                                                         │
│   Data     │                                                         │
│            │                                                         │
│ ─────────  │                                                         │
│ ↩ Peta     │                                                         │
│   publik   │                                                         │
│ ⏻ Keluar   │                                                         │
└────────────┴─────────────────────────────────────────────────────────┘
 sidebar 240px, latar ink, teks putih; di mobile jadi laci geser
```

### 10.2 Dashboard Operator

Ringkas saja — tugas utama Operator adalah menginput, bukan menganalisis.

```
┌─────────────────────────────────────────────────────────────────────┐
│  Halo, Maria. 3 data menunggu verifikasi Admin.                     │
│                                                                     │
│  ┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐     │
│  │ Data terinput    │ │ Menunggu         │ │ Terverifikasi    │     │
│  │                  │ │ verifikasi       │ │                  │     │
│  │       27         │ │        3         │ │       24         │     │
│  │ rumah tangga     │ │ ● perlu diperiksa│ │ ● tampil di peta │     │
│  └──────────────────┘ └──────────────────┘ └──────────────────┘     │
│                                                                     │
│  ┌───────────────────────────────────────┐  ┌────────────────────┐  │
│  │  Input terakhir                       │  │  ＋ Input data     │  │
│  │  ───────────────────────────────────  │  │     warga baru     │  │
│  │  Yohanis Kaize   Jalur B  ◆ Tinggi    │  │                    │  │
│  │                  Menunggu verifikasi  │  │  Tombol besar,     │  │
│  │  Siti Rahayu     Poros    ▲ Sedang    │  │  aksi utama        │  │
│  │                  Terverifikasi        │  │  halaman ini       │  │
│  │  … (5 baris)                          │  └────────────────────┘  │
│  │  Lihat semua data saya →              │                          │
│  └───────────────────────────────────────┘                          │
└─────────────────────────────────────────────────────────────────────┘
```

Di mobile, tombol "Input data warga baru" menjadi tombol mengambang di kanan bawah.

### 10.3 Daftar Data Saya

Tabel sederhana: Nama KK · Jalur · Total Skor · Klasifikasi · Status · Aksi.
Tombol **Ubah** hanya muncul pada baris berstatus *Menunggu verifikasi*. Pada baris *Terverifikasi*, ganti dengan tombol **Lihat** dan teks bantuan "Sudah diverifikasi. Hubungi Admin bila perlu koreksi."
Di mobile tabel berubah menjadi kartu bertumpuk.

### 10.4 Formulir Survei Cerdas

**Rute:** `/operator/data/create` · Satu halaman, tiga bagian bernomor, dengan indikator kemajuan yang menempel di atas.

Prinsip: **satu jawaban = satu ketukan**. Tidak ada kolom teks bebas di bagian kuesioner. Semua pertanyaan variabel SVI memakai radio kartu penuh lebar, bukan dropdown, karena di lapangan dropdown butuh dua ketukan dan menutupi layar.

#### Kerangka halaman

```
┌────────────────────────────────────────────────────────────────┐
│  ← Kembali          Input Data Warga Baru                      │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  ① Identitas   ─────  ② Lokasi & Foto  ─────  ③ Kuesioner│  │  ← lengket
│  └──────────────────────────────────────────────────────────┘  │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  BAGIAN 1 — Identitas & Lokasi Administratif             │  │
│  │  …                                                       │  │
│  └──────────────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  BAGIAN 2 — Spasial & Dokumentasi                        │  │
│  │  …                                                       │  │
│  └──────────────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  BAGIAN 3 — Kuesioner SVI (9 variabel)                   │  │
│  │  …                                                       │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  9 dari 9 pertanyaan terisi        [Batal] [Simpan Data] │  │  ← bilah lengket bawah
│  └──────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────┘
```

Indikator bagian di atas bukan penunjuk langkah wizard — halaman tetap satu gulungan panjang. Menekan indikator menggulir ke bagian tersebut, dan indikator menyala saat bagiannya lengkap.

#### Bagian 1 — Identitas & Lokasi Administratif

```
┌──────────────────────────────────────────────────────────────┐
│  ① Identitas & Lokasi Administratif                          │
│                                                              │
│  Nama kepala keluarga *                                      │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ Contoh: Yohanis Kaize                                  │  │
│  └────────────────────────────────────────────────────────┘  │
│  Tulis sesuai kartu keluarga, tanpa gelar.                   │
│                                                              │
│  Jalur / wilayah permukiman *                                │
│  ┌──────────────┐ ┌──────────────┐ ┌────────────────────┐    │
│  │   Jalur A    │ │   Jalur B    │ │   Poros Trans      │    │
│  └──────────────┘ └──────────────┘ └────────────────────┘    │
│      radio kartu, satu baris di desktop, tumpuk di mobile    │
│                                                              │
│  Alamat detail *                                             │
│  ┌───────────┐ ┌───────────┐ ┌────────────────────────────┐  │
│  │ RT  ▾     │ │ RW  ▾     │ │ No. rumah                  │  │
│  └───────────┘ └───────────┘ └────────────────────────────┘  │
│  RT 01–12, RW 01–03 sesuai wilayah administratif kampung.    │
│                                                              │
│  Patokan lokasi (opsional)                                   │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ Contoh: dekat gereja, sebelah kios Pak Amir            │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

Catatan implementasi: `alamat` di basis data adalah satu kolom teks. Frontend memecahnya jadi RT, RW, nomor rumah, dan patokan agar cepat diisi; penggabungan menjadi satu string dilakukan di sisi server. Isi dropdown RT dengan 01–12 dan RW dengan 01–03 sesuai data kependudukan kampung.

#### Bagian 2 — Spasial & Dokumentasi

```
┌──────────────────────────────────────────────────────────────┐
│  ② Spasial & Dokumentasi                                     │
│                                                              │
│  Titik koordinat rumah *                                     │
│  ┌────────────────────────────────────────────────────────┐  │
│  │        ⌖  Dapatkan Titik Koordinat (GPS)               │  │  ← tombol utama, 52px
│  └────────────────────────────────────────────────────────┘  │
│  Berdirilah di depan rumah responden sebelum menekan tombol. │
│                                                              │
│  ┌─────────────────────────┐ ┌─────────────────────────────┐ │
│  │ Latitude    (nonaktif)  │ │ Longitude    (nonaktif)     │ │
│  │ -8.44271                │ │ 140.86935                   │ │
│  └─────────────────────────┘ └─────────────────────────────┘ │
│  Akurasi ±6 meter · diambil 14:32 · WGS 84                   │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐  │
│  │                                                        │  │
│  │      Peta pratinjau 240px — marker dapat digeser       │  │
│  │                        ⌖                               │  │
│  │                                                        │  │
│  └────────────────────────────────────────────────────────┘  │
│  Geser penanda bila posisinya meleset dari rumah.            │
│                                                              │
│  Foto rumah *                                                │
│  ┌────────────────────────────────────────────────────────┐  │
│  │   📷  Ambil foto            📁  Pilih dari galeri       │  │
│  │   Tampak depan rumah, seluruh bangunan terlihat.        │  │
│  │   JPG/PNG, maksimal 5 MB.                               │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌──────────────────────┐                                    │
│  │  [pratinjau 4:3]  ×  │  rumah-yohanis.jpg · 1,2 MB        │
│  └──────────────────────┘                                    │
└──────────────────────────────────────────────────────────────┘
```

Perilaku tombol GPS:

| Keadaan | Yang tampil |
|---|---|
| Awal | Tombol aktif; kolom lat/lon kosong dan nonaktif |
| Sedang mengambil | Tombol berubah "Mengambil lokasi…" + spinner, tombol terkunci |
| Berhasil | Lat/lon terisi (5–6 desimal, nonaktif), akurasi + jam tampil, peta pratinjau muncul dengan penanda dapat digeser, tombol berubah menjadi "Ambil ulang koordinat" |
| Izin ditolak | Pesan: "Akses lokasi ditolak. Aktifkan izin lokasi di pengaturan peramban, lalu coba lagi." + tautan "Isi koordinat manual" yang membuka lat/lon untuk diketik |
| Gagal / *timeout* | "Sinyal GPS belum didapat. Pastikan berada di luar ruangan, lalu coba lagi." + tombol coba lagi |
| Akurasi > 50 m | Peringatan kuning: "Akurasi ±{n} meter. Tunggu beberapa detik lalu ambil ulang agar titik lebih tepat." (tidak memblokir penyimpanan) |

Gunakan `navigator.geolocation.getCurrentPosition` dengan `enableHighAccuracy: true`, `timeout: 15000`. Fitur ini hanya jalan di HTTPS atau `localhost` — cantumkan itu di README proyek.

Menggeser penanda pada peta pratinjau memperbarui nilai lat/lon dan mengubah label sumber menjadi "disesuaikan manual".

Perilaku unggah foto: `accept="image/*"` dengan `capture="environment"` pada tombol "Ambil foto". Tampilkan pratinjau, nama berkas, dan ukuran. Kompres di sisi peramban hingga sisi terpanjang 1600px sebelum dikirim — koneksi 4G perbatasan tidak stabil. Berkas > 5 MB ditolak dengan pesan jelas.

#### Bagian 3 — Kuesioner SVI (9 variabel)

Sembilan variabel sesuai Tabel 3.2 naskah. Setiap variabel = satu blok pertanyaan dengan tiga radio kartu. **Urutan pilihan selalu dari kondisi paling baik ke paling rentan**, konsisten di seluruh sembilan variabel, supaya pola ketukan kader tidak berubah-ubah.

Jangan menampilkan angka skor (1/2/3) di formulir. Kader mencatat kondisi, bukan menilai orang.

```
┌──────────────────────────────────────────────────────────────┐
│  ③ Kuesioner SVI            0 dari 9 pertanyaan terisi       │
│                                                              │
│  1. Berapa rata-rata total pendapatan keluarga dalam         │
│     satu bulan?                                        [ 1/9 ]│
│  ┌────────────────────────────────────────────────────────┐  │
│  │ ○  Rp2.500.000 ke atas per bulan                       │  │
│  ├────────────────────────────────────────────────────────┤  │
│  │ ○  Rp1.000.000 – Rp2.499.999 per bulan                 │  │
│  ├────────────────────────────────────────────────────────┤  │
│  │ ●  Kurang dari Rp1.000.000 per bulan                   │  │
│  └────────────────────────────────────────────────────────┘  │
│      kartu terpilih: batas brand 2px + latar brand/6%        │
└──────────────────────────────────────────────────────────────┘
```

Daftar lengkap pertanyaan dan pilihan — pakai teks ini persis:

| # | Field | Pertanyaan | Pilihan (urut: baik → rentan) |
|---|---|---|---|
| 1 | `pendapatan` | Berapa rata-rata total pendapatan keluarga dalam satu bulan? | Rp2.500.000 ke atas per bulan · Rp1.000.000 – Rp2.499.999 per bulan · Kurang dari Rp1.000.000 per bulan |
| 2 | `pekerjaan` | Apa pekerjaan atau mata pencaharian utama kepala keluarga? | PNS / TNI / POLRI / Guru PNS / Pegawai bergaji tetap / Karyawan tetap / Wiraswasta mandiri · Petani / Peternak / Pedagang kecil / Pegawai honorer / Pegawai kontrak · Tidak bekerja / Ibu rumah tangga / Buruh harian lepas / Pekerja serabutan |
| 3 | `pendidikan` | Apa jenjang pendidikan formal tertinggi di dalam rumah tangga ini? | Perguruan tinggi (Diploma/S1/S2) · Tamat SMA / sederajat · SD / SMP / tidak tamat sekolah |
| 4 | `jumlah_anggota_keluarga` | Berapa jumlah orang yang tinggal dan menjadi tanggungan di rumah ini? | 1 – 2 orang · 3 – 4 orang · Lebih dari 4 orang |
| 5a | `jenis_dinding` | Bagaimana material utama dinding rumah? | Tembok · Kayu dan tembok (campuran) · Kayu |
| 5b | `jenis_lantai` | Bagaimana material utama lantai rumah? | Keramik / ubin · Semen · Tanah / papan / kayu |
| 5c | `jenis_atap` | Bagaimana material utama atap rumah? *(opsional — data pendukung)* | Seng / genteng · Seng bekas / campuran · Daun / terpal |
| 6 | `fasilitas_sanitasi` | Di mana anggota keluarga biasanya buang air besar? | MCK pribadi / jamban sehat · MCK umum / jamban berbagi dengan tetangga · Tidak punya sanitasi / tidak punya MCK |
| 7 | `sumber_air` | Dari mana sumber air utama untuk minum, memasak, dan mencuci? | PDAM · Sumur bor / galon · Sumur gali |
| 8 | `pendapatan_tambahan` | Apakah keluarga memiliki sumber pemasukan lain di luar pekerjaan utama? | Usaha kecil atau pekerjaan sampingan aktif (warung, jual hasil kebun, peternakan) · Hanya menerima bantuan pemerintah · Tidak ada pendapatan tambahan |
| 9 | `kepemilikan_lahan` | Apa status kepemilikan rumah dan lahan yang ditempati saat ini? | Milik sendiri / warisan keluarga · Pinjam pakai / sewa / garapan · Tidak memiliki lahan |

Catatan khusus variabel 5 (Kondisi Fisik Bangunan): variabel ini komposit dari **dinding dan lantai**. Di layar, tampilkan 5a dan 5b berdampingan dalam satu kartu berjudul "Kondisi fisik bangunan", dan hitung keduanya sebagai **satu** dari sembilan pertanyaan pada penghitung kemajuan. Pertanyaan atap (5c) ditempatkan di kartu yang sama namun diberi label "Data pendukung — tidak masuk perhitungan skor", karena naskah menetapkan atap sebagai data deskriptif.

#### Validasi dan penyimpanan

- Validasi berjalan saat pengguna meninggalkan field (*on blur*), bukan saat mengetik.
- Kesalahan tampil di bawah field: teks 13px warna `tinggi-tx` + batas input `tinggi`. Jangan pakai `alert()`.
- Menekan "Simpan Data" dengan isian kurang: gulirkan ke field bermasalah pertama, beri fokus, dan tampilkan ringkasan di bilah bawah: "3 isian belum lengkap." Tombol tetap dapat ditekan — jangan menonaktifkannya, karena tombol mati tanpa penjelasan membingungkan di lapangan.
- Sedang menyimpan: tombol berubah "Menyimpan…" + spinner, formulir terkunci.
- Berhasil: alihkan ke `/operator/data` + toast hijau **"Data berhasil disimpan dan menunggu verifikasi Admin."** Baris baru tersorot 2 detik.
- Gagal jaringan: pertahankan seluruh isian, tampilkan toast merah "Data gagal dikirim. Periksa koneksi lalu tekan Simpan lagi." Isian tidak boleh hilang.
- Bila pengguna menekan tombol kembali dengan isian belum tersimpan: modal konfirmasi "Data yang sudah diisi akan hilang. Tetap keluar?"

#### Mode ubah

Halaman yang sama dengan judul "Ubah Data Warga" dan seluruh field terisi. Tambahkan di atas formulir: baris status + peringatan "Perubahan akan menghitung ulang skor SVI secara otomatis." Bila status sudah *Terverifikasi* dan pengguna adalah Operator, formulir dibuka dalam mode baca saja.

---

## 11. Halaman Admin — Kendali mutu

**Rute:** `/admin`, `/admin/verifikasi`, `/admin/data`, `/admin/log` · **Layout:** `layouts/app`

Sidebar Admin: Dashboard · Antrean Verifikasi (dengan lencana jumlah) · Semua Data · Log Pembaruan · Peta publik · Keluar.

### 11.1 Dashboard Admin

```
┌───────────────────────────────────────────────────────────────────────┐
│  Dashboard Admin                                                      │
│                                                                       │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐          │
│  │ Total data │ │ Menunggu   │ │ Terveri-   │ │ Kerentanan │          │
│  │            │ │ verifikasi │ │ fikasi     │ │ tinggi     │          │
│  │     91     │ │     7      │ │     84     │ │     24     │          │
│  │ rumah      │ │ Periksa →  │ │ di peta    │ │ ◆ 26,4%    │          │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘          │
│                                                                       │
│  ┌───────────────────────────────┐ ┌─────────────────────────────┐    │
│  │ Antrean verifikasi terbaru    │ │ Sebaran kerentanan          │    │
│  │ ───────────────────────────── │ │   (pie chart ringkas)       │    │
│  │ Yohanis Kaize  Jalur B  1 jam │ │      ●  Rendah      7       │    │
│  │ Siti Rahayu    Poros    2 jam │ │      ▲  Sedang     60       │    │
│  │ … 5 baris                     │ │      ◆  Tinggi     24       │    │
│  │ Buka antrean verifikasi →     │ │                             │    │
│  └───────────────────────────────┘ └─────────────────────────────┘    │
└───────────────────────────────────────────────────────────────────────┘
```

Kartu "Menunggu verifikasi" adalah pintu masuk utama halaman ini — buat seluruh kartunya dapat diklik menuju `/admin/verifikasi`.

### 11.2 Antrean Verifikasi

```
┌────────────────────────────────────────────────────────────────────────────┐
│  Antrean Verifikasi                                    7 data menunggu     │
│                                                                            │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ [Semua] [Menunggu verifikasi] [Terverifikasi]   🔍 Cari nama KK…     │  │
│  │ Jalur: [Semua ▾]   Klasifikasi: [Semua ▾]                            │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                            │
│  ┌────┬─────────────────┬────────┬──────┬─────────────┬────────┬────────┐  │
│  │Foto│ Nama KK         │ Jalur  │ Skor │ Klasifikasi │ Status │ Aksi   │  │
│  ├────┼─────────────────┼────────┼──────┼─────────────┼────────┼────────┤  │
│  │ 🖼 │ Yohanis Kaize   │ B      │  20  │ ▲ Sedang    │ Menung-│ Periksa│  │
│  │48px│ RT03/RW01 · Maria│       │      │             │ gu     │  ⋮     │  │
│  ├────┼─────────────────┼────────┼──────┼─────────────┼────────┼────────┤  │
│  │ 🖼 │ Siti Rahayu     │ Poros  │  13  │ ● Rendah    │ Menung-│ Periksa│  │
│  │    │ RT07/RW02 · Maria│       │      │             │ gu     │  ⋮     │  │
│  ├────┼─────────────────┼────────┼──────┼─────────────┼────────┼────────┤  │
│  │ 🖼 │ Petrus Gebze    │ A      │  23  │ ◆ Tinggi    │ Terve- │ Lihat  │  │
│  │    │ RT01/RW01 · Yosep│       │      │             │ rifikasi│ ⋮     │  │
│  └────┴─────────────────┴────────┴──────┴─────────────┴────────┴────────┘  │
│                                                                            │
│  Menampilkan 1–10 dari 91          [10 ▾] baris     ‹ 1 2 3 … 10 ›         │
└────────────────────────────────────────────────────────────────────────────┘
```

Ketentuan tabel:
- Kolom foto berisi *thumbnail* 48px persegi; klik memperbesar. Ini sengaja ditaruh di kolom pertama karena tugas Admin bersifat visual.
- Kolom "Nama KK" berisi baris kedua kecil: alamat singkat + nama Operator penginput.
- Baris berstatus *Menunggu verifikasi* diberi garis kiri 3px warna `sedang` agar terpisah dari baris terverifikasi.
- Bawaan pengurutan: yang paling lama menunggu di atas.
- Menu `⋮` berisi: Ubah data · Hapus data.
- Tanpa aksi massal. Verifikasi harus per data, karena inti pekerjaannya membandingkan foto dengan jawaban.

### 11.3 Modal Validasi berdampingan

Ini komponen terpenting halaman Admin. Tugas Admin menurut naskah adalah memeriksa tiga hal: kelengkapan 9 variabel, kesesuaian jawaban fisik bangunan dengan foto, dan kewajaran titik koordinat.

```
┌──────────────────────────────────────────────────────────────────────────────┐
│  Periksa Data — Yohanis Kaize            Menunggu verifikasi           [×]   │
├────────────────────────────────────┬─────────────────────────────────────────┤
│                                    │  Identitas                              │
│   ┌──────────────────────────────┐ │  Nama KK      Yohanis Kaize             │
│   │                              │ │  Jalur        Jalur B                   │
│   │                              │ │  Alamat       RT 03 / RW 01 No. 12      │
│   │      FOTO RUMAH              │ │  Diinput oleh Maria K. · 4 Sep, 14:32   │
│   │      (dapat diperbesar,      │ │  ─────────────────────────────────────  │
│   │       zoom & geser)          │ │  ⚠ Periksa terhadap foto                │
│   │                              │ │  Dinding      Kayu                      │
│   │                              │ │  Lantai       Semen                     │
│   └──────────────────────────────┘ │  Atap         Seng                      │
│   [🔍 Perbesar]  [↻ Putar]         │  ─────────────────────────────────────  │
│                                    │  Jawaban 9 variabel SVI                 │
│   ┌──────────────────────────────┐ │  Pendapatan   < Rp1.000.000        (3)  │
│   │                              │ │  Pekerjaan    Petani               (2)  │
│   │   PETA TITIK KOORDINAT       │ │  Pendidikan   Tidak tamat sekolah  (3)  │
│   │   -8.44271, 140.86935        │ │  Anggota kel. 1 orang              (1)  │
│   │   (batas kampung ditumpuk)   │ │  Fisik bangunan Kayu + Semen       (2)  │
│   │                              │ │  Sanitasi     Jamban berbagi       (2)  │
│   └──────────────────────────────┘ │  Sumber air   Sumur gali           (3)  │
│   ✓ Titik berada di dalam wilayah  │  Pend. tambahan Tidak ada          (3)  │
│     permukiman Kampung Sota        │  Kepemilikan  Milik sendiri        (1)  │
│                                    │  ─────────────────────────────────────  │
│                                    │  Total Skor SVI    20  (skala 9–27)     │
│                                    │  Skor rata-rata    2,22                 │
│                                    │  Klasifikasi       ▲ Kerentanan Sedang  │
│                                    │  Skor dihitung otomatis oleh sistem     │
│                                    │  dan tidak dapat diubah manual.         │
├────────────────────────────────────┴─────────────────────────────────────────┤
│  [Hapus data]                      [Revisi data]   [✓ Verifikasi & tampilkan]│
└──────────────────────────────────────────────────────────────────────────────┘
   lebar 1100px · dua kolom 50/50 · di layar <1024px kolom bertumpuk
```

Detail perilaku:
- Baris **Dinding / Lantai / Atap** dipisahkan dan diberi penanda "Periksa terhadap foto" karena itulah pasangan data yang paling sering tidak cocok. Beri latar `paper` pada blok tersebut.
- Blok skor di bawah kanan bersifat informatif dan **tidak dapat diedit**. Sertakan kalimat penjelas agar penguji sidang langsung melihat bahwa skor tidak ditentukan manusia.
- Peta kecil menampilkan titik rumah di atas basemap. Bila di luar batas wajar, ganti tanda centang dengan peringatan merah: "Titik berada di luar wilayah permukiman. Periksa ulang koordinat."
- **Verifikasi & tampilkan** → modal tertutup, baris hilang dari filter "Menunggu verifikasi", toast: "Data Yohanis Kaize terverifikasi dan kini tampil di peta publik."
- **Revisi data** → arahkan ke `/admin/data/{id}/edit`, formulir yang sama dengan milik Operator, dengan spanduk kuning di atas: "Anda sedang merevisi data yang menunggu verifikasi. Skor akan dihitung ulang otomatis setelah disimpan."
- **Hapus data** → modal konfirmasi kedua yang mengharuskan mengetik nama KK, lalu tombol merah "Hapus permanen". Toast: "Data Yohanis Kaize dihapus."
- Navigasi antar data: tombol "‹ Sebelumnya" dan "Berikutnya ›" di pojok bawah modal agar Admin dapat memeriksa antrean berturut-turut tanpa menutup modal.

### 11.4 Semua Data

Tabel serupa Antrean Verifikasi tanpa penyaring status bawaan, ditambah tombol "＋ Tambah data warga" dan tombol "Ekspor" (Admin juga berhak ekspor).

### 11.5 Log Pembaruan

Tabel baca-saja: Waktu · Pengguna (nama + peran) · Aksi · Data terkait · Detail.
Kolom Aksi memakai lencana: `Tambah` (netral), `Ubah` (biru abu), `Verifikasi` (palet rendah), `Hapus` (palet tinggi).
Tombol "Detail" membuka modal berisi perbandingan sebelum/sesudah dalam dua kolom, dengan baris yang berubah disorot. Tampilkan sebagai daftar label–nilai yang mudah dibaca, bukan JSON mentah.
Penyaring: rentang tanggal, pengguna, jenis aksi.

---

## 12. Halaman Kepala Kampung — Dashboard eksekutif

**Rute:** `/pimpinan`, `/pimpinan/laporan` · **Layout:** `layouts/app`

Sidebar: Dashboard · Laporan · Peta publik · Keluar. Tidak ada menu input, verifikasi, atau log.

Halaman ini dipakai untuk mengambil keputusan bantuan sosial dan kemungkinan besar akan menjadi tangkapan layar utama pada BAB IV serta demo sidang. Buat rapi dan dapat dicetak.

### 12.1 Wireframe

```
┌────────────────────────────────────────────────────────────────────────────┐
│  Dashboard Kerentanan Sosial Ekonomi        Data per 4 September 2026      │
│  Kampung Sota, Distrik Sota, Kabupaten Merauke   [⤓ Unduh laporan]         │
│                                                                            │
│  ┌───────────────┐ ┌───────────────┐ ┌───────────────┐ ┌───────────────┐   │
│  │ Rumah tangga  │ │ ● Kerentanan  │ │ ▲ Kerentanan  │ │ ◆ Kerentanan  │   │
│  │ terdata       │ │   Rendah      │ │   Sedang      │ │   Tinggi      │   │
│  │      91       │ │      7        │ │      60       │ │      24       │   │
│  │ terverifikasi │ │    7,7%       │ │    65,9%      │ │    26,4%      │   │
│  └───────────────┘ └───────────────┘ └───────────────┘ └───────────────┘   │
│                                                                            │
│  ┌────────────────────────────┐  ┌──────────────────────────────────────┐  │
│  │ Persentase tingkat         │  │ Sebaran per wilayah                  │  │
│  │ kerentanan                 │  │                                      │  │
│  │                            │  │  Jalur A  ███████░░░░░░░  15         │  │
│  │        ╭─────╮             │  │  Jalur B  ████████████░░  33         │  │
│  │       │ pie  │             │  │  Poros    ███████████████ 43         │  │
│  │        ╰─────╯             │  │           ■ Rendah ■ Sedang ■ Tinggi │  │
│  │   ● 7,7  ▲ 65,9  ◆ 26,4    │  │      (bar bertumpuk, 3 kategori)     │  │
│  └────────────────────────────┘  └──────────────────────────────────────┘  │
│                                                                            │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Peta sebaran kerentanan                          Buka peta penuh →   │  │
│  │                                                                      │  │
│  │            (Leaflet, tinggi 380px, hanya baca)                       │  │
│  │                                                                      │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                            │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Rumah tangga kerentanan tinggi                    Lihat semua (24) → │  │
│  │  Nama KK          Jalur    Alamat           Total Skor  Klasifikasi  │  │
│  │  Petrus Gebze     Jalur A  RT01/RW01           23       ◆ Tinggi     │  │
│  │  … 5 baris teratas                                                   │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────────────┘
```

### 12.2 Ketentuan grafik

- **Pie chart** — tiga irisan dengan warna klasifikasi. Label di dalam irisan berisi persentase; legenda di bawah berisi nama kelas + jumlah + persentase. Tanpa efek 3D, tanpa donat berlubang besar.
- **Bar chart** — batang bertumpuk per wilayah (Jalur A, Jalur B, Poros Trans), setiap batang terbagi tiga kelas kerentanan. Sumbu Y jumlah rumah tangga. Ini menjawab pertanyaan sebenarnya: *jalur mana yang paling perlu diprioritaskan*.
- Kedua grafik menampilkan tooltip saat disentuh, dan menyediakan tabel angka di bawahnya untuk pembaca yang tidak dapat membaca grafik.
- Semua grafik hanya menghitung data berstatus **Terverifikasi**. Tulis itu sebagai keterangan kecil di bawah setiap grafik.

### 12.3 Panel Laporan

```
┌───────────────────────────────────────────────────────────────────┐
│  Unduh Laporan Rekapitulasi                                       │
│                                                                   │
│  Wilayah          [ Semua wilayah ▾ ]                             │
│  Tingkat          ☑ Rendah   ☑ Sedang   ☑ Tinggi                  │
│  Kolom disertakan ☑ Identitas & alamat                            │
│                   ☑ Titik koordinat (Lat/Lon)                     │
│                   ☑ Rincian skor 9 variabel                       │
│                   ☑ Total skor & klasifikasi                      │
│                                                                   │
│  Laporan akan berisi 91 rumah tangga terverifikasi.               │
│                                                                   │
│  ┌──────────────────────┐  ┌──────────────────────┐               │
│  │  ⤓  Unduh PDF        │  │  ⤓  Unduh Excel      │               │
│  │  Untuk dicetak dan   │  │  Untuk diolah        │               │
│  │  diarsipkan          │  │  kembali             │               │
│  └──────────────────────┘  └──────────────────────┘               │
│                                                                   │
│  ─────────────────────────────────────────────────────────────    │
│  Riwayat unduhan                                                  │
│  Rekap_Kerentanan_Sota_2026-09-04.pdf   4 Sep 2026, 09:12         │
└───────────────────────────────────────────────────────────────────┘
```

Isi kolom laporan sesuai naskah: identitas warga (nama KK, jalur, RT/RW), titik koordinat, rincian skor 9 variabel, total skor, dan klasifikasi.
Saat tombol ditekan: tombol berubah "Menyiapkan berkas…" dengan spinner, lalu toast "Laporan siap diunduh." Pada tahap casing, hasilkan berkas contoh atau cukup tampilkan state-nya.

---

## 13. Komponen bersama

### 13.1 Toast

Muncul di kanan atas (desktop) atau atas layar (mobile), lebar maksimal 400px, hilang otomatis setelah 5 detik, dapat ditutup manual. Tiga jenis: berhasil (palet rendah), peringatan (palet sedang), gagal (palet tinggi). Satu baris kalimat, tanpa judul terpisah.

### 13.2 Modal

Latar gelap `ink` 50%, kotak putih terpusat, radius 10px. Tutup dengan tombol ×, tombol Batal, tombol Esc, atau klik latar — kecuali modal hapus, yang hanya dapat ditutup lewat tombol. Fokus terkunci di dalam modal selama terbuka.

### 13.3 Keadaan kosong

| Layar | Judul | Kalimat pendukung | Aksi |
|---|---|---|---|
| Data Saya kosong | Belum ada data warga | Mulai dengan menginput rumah tangga pertama. | ＋ Input data warga baru |
| Antrean verifikasi kosong | Tidak ada antrean | Semua data sudah diverifikasi. | Lihat semua data |
| Hasil pencarian kosong | Tidak ada hasil untuk "…" | Coba potongan nama yang lain. | Hapus pencarian |
| Filter menyisakan nol | Tidak ada data pada filter ini | Ubah pilihan wilayah atau tingkat kerentanan. | Atur ulang filter |

### 13.4 Memuat

Gunakan rangka abu-abu (*skeleton*) untuk kartu statistik, baris tabel, dan grafik. Jangan memakai spinner layar penuh — kecuali pengambilan GPS dan penyiapan berkas laporan.

### 13.5 Kesalahan

Kesalahan jaringan tampil sebagai kartu di dalam area yang gagal, bukan menghapus seluruh halaman. Kalimat menjelaskan apa yang gagal dan apa yang harus dilakukan, tanpa kode teknis dan tanpa permintaan maaf berlebihan. Contoh: "Grafik gagal dimuat. Muat ulang halaman untuk mencoba lagi."

---

## 14. Responsivitas dan kondisi lapangan

| Titik henti | Perilaku |
|---|---|
| < 640px | Satu kolom. Sidebar jadi laci. Tabel jadi kartu bertumpuk. Panel peta jadi lembar bawah. Formulir satu kolom penuh. |
| 640–1023px | Kartu statistik 2 kolom. Modal validasi bertumpuk (foto di atas, data di bawah). |
| ≥ 1024px | Tata letak penuh sesuai wireframe. Sidebar tetap. Modal validasi dua kolom. |

Pertimbangan khusus lapangan, karena alat ini dipakai kader kampung di luar ruangan:

1. **Kontras tinggi.** Teks isi minimal rasio 4.5:1 terhadap latar. Hindari teks abu muda di atas putih pada formulir operator.
2. **Target sentuh besar.** Radio kartu pada kuesioner minimal 52px, jarak antar kartu 8px agar tidak salah tekan.
3. **Hemat data.** Kompres foto di peramban sebelum unggah. Muat Leaflet dan Chart.js hanya di halaman yang memerlukannya.
4. **Toleran koneksi putus.** Isian formulir disimpan sementara ke memori halaman; jika pengiriman gagal, isian tidak hilang. (Penyimpanan luring penuh berada di luar lingkup versi ini.)
5. **Satu tangan.** Aksi utama di layar sempit berada di bagian bawah, dalam jangkauan ibu jari.

---

## 15. Aksesibilitas

- Setiap input punya `<label>` yang tertaut, bukan sekadar *placeholder*.
- Kelompok radio dibungkus `<fieldset>` dengan `<legend>` berisi teks pertanyaan.
- Marker peta dapat dijangkau keyboard dan punya `aria-label`: "Rumah tangga Yohanis Kaize, Kerentanan Sedang, Jalur B."
- Warna tidak pernah menjadi satu-satunya pembawa makna: klasifikasi selalu disertai teks dan bentuk; status selalu disertai teks.
- Sediakan tautan "Lompat ke konten utama" pada layout panel internal.
- Modal mengembalikan fokus ke elemen pemicu saat ditutup.
- Grafik disertai tabel angka setara di dekatnya.

---

## 16. Data contoh untuk tahap casing

Gunakan angka ini agar tampilan konsisten dengan hasil penelitian:

```
Total rumah tangga terdata     : 91
Kerentanan Rendah              : 7   (7,7%)
Kerentanan Sedang              : 60  (65,9%)
Kerentanan Tinggi              : 24  (26,4%)

Sebaran per wilayah (Rendah / Sedang / Tinggi):
  Jalur A      15 rumah tangga   →  0 / 7 / 8
  Jalur B      33 rumah tangga   →  4 / 21 / 8
  Poros Trans  43 rumah tangga   →  3 / 32 / 8

Contoh satu baris data:
  nama_kk            : Yohanis Kaize
  jalur              : Jalur B
  alamat             : RT 03 / RW 01 No. 12
  latitude           : -8.44271
  longitude          : 140.86935
  pendapatan         : Kurang dari Rp1.000.000 per bulan        (skor 3)
  pekerjaan          : Petani                                   (skor 2)
  pendidikan         : Tidak tamat sekolah                      (skor 3)
  jumlah_anggota     : 1 orang                                  (skor 1)
  dinding / lantai   : Kayu (3) / Semen (2)  → fisik bangunan   (skor 2)
  fasilitas_sanitasi : Jamban berbagi                           (skor 2)
  sumber_air         : Sumur gali                               (skor 3)
  pendapatan_tambahan: Tidak ada                                (skor 3)
  kepemilikan_lahan  : Milik sendiri                            (skor 1)
  total_skor_svi     : 20
  skor_rata_rata     : 2,22
  klasifikasi        : Kerentanan Sedang
  status_verifikasi  : menunggu_verifikasi
```

Contoh perhitungan di atas diambil dari Tabel 3.4 naskah, sehingga tangkapan layar sistem dapat langsung disandingkan dengan tabel di BAB III.

Buat 12–15 baris data contoh dengan nama yang wajar untuk Merauke, tersebar di tiga wilayah dan tiga kelas kerentanan, agar peta dan grafik terlihat nyata saat demo.

---

## 17. Kriteria penerimaan

Casing dianggap selesai bila seluruh butir berikut terpenuhi.

**Peta publik**
- [ ] Peta memenuhi layar penuh dan dapat digeser serta diperbesar
- [ ] Marker tampil dalam tiga warna sekaligus tiga bentuk berbeda
- [ ] Kotak centang filter kerentanan dan wilayah mengubah titik yang tampil seketika
- [ ] Pencarian nama KK memunculkan saran dan menerbangkan peta ke titik terpilih
- [ ] Klik marker membuka modal berisi foto, nama KK, total skor, dan klasifikasi
- [ ] Halaman dapat dipakai tanpa login dan tidak menampilkan menu internal apa pun

**Operator**
- [ ] Dashboard menampilkan tiga kartu metrik dan daftar input terakhir
- [ ] Formulir terbagi tiga bagian dengan indikator kemajuan yang lengket
- [ ] Tombol GPS mengisi lat/lon nonaktif dan menampilkan peta pratinjau dengan penanda dapat digeser
- [ ] Semua state GPS (berhasil, ditolak, gagal, akurasi rendah) dapat ditampilkan
- [ ] Unggah foto menampilkan pratinjau, nama berkas, dan ukuran
- [ ] Sembilan variabel memakai radio kartu, tanpa angka skor yang terlihat
- [ ] Penghitung "n dari 9 pertanyaan terisi" berjalan
- [ ] Simpan berhasil memunculkan toast dan mengalihkan ke daftar data
- [ ] Tombol Ubah tersembunyi pada baris berstatus Terverifikasi

**Admin**
- [ ] Tabel antrean menampilkan thumbnail foto, lencana status, penyaring, dan paginasi
- [ ] Modal validasi menyandingkan foto dan peta di kiri dengan data survei di kanan
- [ ] Blok dinding/lantai/atap ditandai sebagai bagian yang diperiksa terhadap foto
- [ ] Blok skor tampil read-only disertai keterangan bahwa skor dihitung otomatis
- [ ] Tombol Verifikasi, Revisi, dan Hapus berfungsi (Hapus memakai konfirmasi ketik nama)
- [ ] Navigasi "Sebelumnya / Berikutnya" berpindah antar data tanpa menutup modal
- [ ] Halaman log menampilkan perbandingan sebelum/sesudah yang mudah dibaca

**Kepala Kampung**
- [ ] Empat kartu metrik menampilkan jumlah dan persentase
- [ ] Pie chart persentase dan bar chart bertumpuk per wilayah tampil dengan warna yang benar
- [ ] Peta ringkas hanya-baca tertanam di dashboard
- [ ] Panel laporan menyediakan penyaring, pilihan kolom, dan dua tombol unduh dengan state "menyiapkan berkas"
- [ ] Tidak ada satu pun tombol input, verifikasi, atau hapus di seluruh area ini

**Menyeluruh**
- [ ] Seluruh teks antarmuka Bahasa Indonesia dan konsisten dengan istilah Bagian 7
- [ ] Semua halaman berfungsi baik pada lebar 360px
- [ ] Cincin fokus keyboard terlihat di seluruh elemen interaktif
- [ ] Tidak ada `alert()`, `confirm()`, atau teks *lorem ipsum* yang tersisa

---

## 18. Urutan pengerjaan yang disarankan

| Tahap | Keluaran | Alasan didahulukan |
|---|---|---|
| 1 | `tailwind.config.js`, layout `app`/`public`/`auth`, komponen dasar (tombol, input, lencana, kartu, modal, toast) | Semua halaman bergantung padanya |
| 2 | Peta publik lengkap dengan filter, pencarian, pop-up | Fitur paling terlihat dan paling sering didemokan |
| 3 | Formulir survei 3 bagian + GPS + unggah foto | Paling rumit dan paling menentukan keberhasilan di lapangan |
| 4 | Dashboard dan daftar data Operator | Melengkapi alur kerja Operator dari ujung ke ujung |
| 5 | Tabel antrean + modal validasi Admin | Membutuhkan komponen dari tahap 3 |
| 6 | Dashboard Kepala Kampung + panel laporan | Berbasis komponen kartu dan grafik yang mandiri |
| 7 | Halaman log, halaman Tentang, penghalusan state kosong/gagal | Pelengkap |

---

## 19. Catatan untuk dikunci sebelum implementasi lanjut

Beberapa hal berikut belum sepenuhnya konsisten di dalam naskah. Antarmuka pada dokumen ini sudah mengambil satu keputusan agar pembangunan tidak tertahan, tetapi sebaiknya dipastikan ulang dengan pembimbing sebelum BAB IV ditulis.

1. **Jumlah status verifikasi.** Sub-bab Analisis Sistem Yang Diusulkan menyebut status ketiga, "Perlu Perbaikan", yang dikembalikan ke Operator. Sementara itu kebutuhan fungsional, Use Case, Activity Diagram, dan struktur tabel `data_penduduk` semuanya menetapkan **dua** status saja, dengan Admin memperbaiki data secara langsung. PRD ini mengikuti yang dua status. Kalimat pada Analisis Sistem Yang Diusulkan perlu diselaraskan.

2. **Objek variabel Pendidikan.** Tabel instrumen survei menanyakan pendidikan terakhir **kepala keluarga**, sedangkan struktur tabel `data_penduduk` menuliskan "jenjang tertinggi rumah tangga". PRD ini memakai jenjang tertinggi di dalam rumah tangga pada teks pertanyaan. Pilih satu dan samakan di kedua tempat.

3. **Cakupan wilayah.** Batasan Masalah menyebut lokasi penelitian adalah wilayah administratif Kampung Sota, sementara Gambaran Umum Objek Penelitian menyebut fokus pada Jalur A dan Jalur B. Kolom `jalur` pada basis data memuat tiga nilai termasuk Poros Trans, dan sebaran hasil memang mencakup 43 responden di Poros Trans. Antarmuka ini memakai tiga wilayah. Kalimat pada Gambaran Umum sebaiknya menyebut ketiganya.

4. **Penomoran tabel.** Tabel Kebutuhan Non-Fungsional saat ini bernomor 3.4, padahal nomor itu sudah dipakai Tabel Contoh Perhitungan Manual. Struktur tabel basis data masih bertanda "Tabel 3.x". Perlu penomoran ulang.

5. **Variabel atap.** Struktur basis data menyediakan kolom `jenis_atap` sebagai data deskriptif di luar perhitungan, dan Tabel 3.2 menyebut Kondisi Fisik Bangunan sebagai rata-rata "tiga sub-indikator" padahal yang dirinci hanya dinding dan lantai. Formulir pada PRD ini tetap menanyakan atap namun menandainya sebagai data pendukung. Kalimat pengantar Tabel 3.2 perlu diperbaiki menjadi dua sub-indikator.
