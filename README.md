# 🎂 Daftar yang Ulang Tahun — Plugin SLiMS

Plugin untuk **Senayan Library Management System (SLiMS) 9** yang membantu pustakawan melihat anggota yang berulang tahun, membagikan tautan ucapan interaktif, menyalin pesan WhatsApp, serta mengirim ucapan melalui email menggunakan konfigurasi SMTP SLiMS.

Halaman ucapan menggunakan **template HTML mandiri** sehingga tidak menampilkan header, navigasi, maupun footer OPAC. Animasi ulang tahun berlangsung bertahap dengan tombol interaktif, lampu, musik, dekorasi, balon nama, kue, lilin, dan pesan khusus.

## Pratinjau

Tambahkan tangkapan layar ke repositori, misalnya `screenshots/daftar-ulang-tahun.png` dan `screenshots/halaman-ucapan.png`, kemudian aktifkan tautan berikut:

<!-- ![Daftar ulang tahun](screenshots/daftar-ulang-tahun.png) -->
<!-- ![Halaman ucapan](screenshots/halaman-ucapan.png) -->

## Fitur

| Fitur | Keterangan |
| --- | --- |
| Menu Keanggotaan | **Keanggotaan → Daftar yang Ulang Tahun** |
| Rentang ulang tahun | Menampilkan anggota yang berulang tahun **kemarin, hari ini, dan besok** berdasarkan tanggal server |
| Informasi anggota | Nomor anggota, nama, institusi, nomor telepon, email, tanggal lahir, dan keterangan waktu ulang tahun |
| Pencarian | Nama, nomor anggota, atau tanggal lahir |
| Pagination | 20 data per halaman |
| Copy URL | Menyalin tautan halaman ucapan personal |
| Copy WA | Menyalin teks ucapan WhatsApp beserta tautannya, untuk dikirim manual |
| Kirim Email | Mengirim email satu per satu melalui SMTP yang telah dikonfigurasi di SLiMS |
| Halaman OPAC | Halaman ucapan mandiri tanpa header/footer SLiMS |
| Animasi interaktif | Tombol berurutan, musik, lampu, dekorasi, balon, kue, lilin, dan pesan |
| Aset lokal | CSS, JavaScript, jQuery, gambar, dan audio berada di folder plugin; tanpa CDN |

## Persyaratan

- SLiMS 9 dengan dukungan plugin admin dan registrasi halaman OPAC.
- PHP **8.1 atau lebih baru**.
- Database SLiMS dengan tabel `member` dan kolom `member_id`, `member_name`, `birth_date`, `inst_name`, `member_phone`, serta `member_email`.
- Untuk pengiriman email: konfigurasi `config('mail')` yang aktif dan pustaka **PHPMailer** atau **Symfony Mailer** yang tersedia pada instalasi SLiMS.

## Instalasi

1. Unduh arsip plugin dan ekstrak folder `daftar_ulang_tahun`.
2. Salin folder tersebut ke direktori `plugins/` pada instalasi SLiMS, misalnya `C:\laragon\www\unimugo\devs\plugins\daftar_ulang_tahun\`.
3. Masuk ke admin SLiMS, buka **Sistem → Plugins**, kemudian aktifkan **Daftar yang Ulang Tahun**.
4. Buka **Keanggotaan → Daftar yang Ulang Tahun**.
5. Uji halaman ucapan menggunakan tombol **Copy URL**, lalu buka tautannya di browser.

Untuk memperbarui dari versi sebelumnya, **cadangkan folder plugin terlebih dahulu**, timpa file plugin dengan versi terbaru, kemudian lakukan **Ctrl + F5** agar CSS dan JavaScript terbaru dimuat.

## Cara menggunakan

### Melihat dan mencari anggota

Daftar menampilkan anggota yang tanggal dan bulan kelahirannya cocok dengan kemarin, hari ini, atau besok. Tahun kelahiran tidak dipakai untuk menentukan hari ulang tahun. Gunakan kolom pencarian untuk menemukan anggota berdasarkan nama, ID, atau tanggal lahir.

### Membagikan ucapan

Pada baris anggota, gunakan:

- **Copy URL** untuk menyalin tautan halaman ucapan.
- **Copy WA** untuk menyalin teks ucapan dan tautannya, lalu tempel ke WhatsApp secara manual.
- **Kirim Email** untuk meminta plugin mengirim ucapan ke alamat email anggota. Pengiriman membutuhkan konfirmasi dan alamat email yang valid.

Contoh URL untuk instalasi di root:

```text
https://domain-perpustakaan.ac.id/index.php?p=happybirthdayto&name=Okta
```

Contoh URL untuk instalasi di subfolder:

```text
http://localhost/unimugo/devs/index.php?p=happybirthdayto&name=Ade%20Ratmasih
```

**Penting:** parameter nama menggunakan `&name=`, **bukan** `?name=` setelah `p=happybirthdayto`.

### Alur animasi halaman ucapan

1. **Turn On Lights** — menyalakan lampu.
2. **Play Music** — memutar musik setelah interaksi pengguna.
3. **Let's Decorate** — menampilkan dekorasi pesta.
4. **Fly With Balloons** — menjalankan animasi balon dengan huruf nama penerima.
5. **Most Delicious Cake Ever** — menampilkan kue.
6. **Light Candle** — menyalakan lilin.
7. **Happy Birthday** — menampilkan ucapan.
8. **A Message for You** — menampilkan pesan bertahap.

Tombol interaksi diposisikan di **bagian bawah layar**. Pengaturan tata letak terakhir berada di `public/birthday-layout-fix.css`.

## Konfigurasi email

Plugin membaca konfigurasi yang sudah tersedia melalui `config('mail')` SLiMS. Parameter yang dikenali meliputi:

```text
enable
server
server_port
auth_enable
auth_username
auth_password
SMTPSecure
from
from_name
```

Pengiriman menggunakan PHPMailer jika tersedia, atau Symfony Mailer sebagai alternatif. Plugin **tidak** menggunakan template keterlambatan `sendOverdueNotice()` dan tidak menyimpan kredensial SMTP di dalam kode plugin.

Jika pengiriman gagal, periksa konfigurasi SMTP SLiMS, alamat pengirim, port, metode keamanan, ketersediaan pustaka mailer, dan log PHP/server. Jangan memasukkan password SMTP atau App Password ke repositori GitHub.

## Struktur folder

```text
daftar_ulang_tahun/
├── daftar_ulang_tahun.plugin.php   # Registrasi menu admin dan halaman OPAC
├── index.php                       # Daftar anggota, pencarian, paging, tombol aksi
├── birthday.php                    # Halaman ucapan HTML mandiri
├── mailer.php                      # Integrasi SMTP SLiMS
├── README.md
└── public/
    ├── effect.js                   # Urutan animasi asli
    ├── birthday-name.js            # Nama penerima pada animasi
    ├── jquery.min.js               # jQuery lokal
    ├── stylesheet.css
    ├── loading.css
    ├── cake.css
    ├── birthday-grid.css
    ├── birthday-layout-fix.css      # Perbaikan posisi tombol dan kue
    ├── hbd.mp3
    └── ...                         # Gambar lampu, balon, banner, dan aset lainnya
```

## Sumber data dan privasi

Data dibaca dari tabel **`member`** pada database SLiMS. Plugin ini tidak membutuhkan tabel database tambahan untuk daftar ulang tahun. Halaman ucapan publik hanya menerima **nama** melalui parameter URL; nomor anggota, email, dan tanggal lahir tidak ditampilkan di halaman tersebut.

Pengiriman email dari admin memakai validasi sesi/CSRF, memeriksa anggota berdasarkan database, memvalidasi alamat email, dan membatasi aksi pada anggota dalam rentang ulang tahun yang ditampilkan. **Nama pada URL bersifat publik**: hindari membagikan tautan apabila penerima tidak menghendaki namanya terlihat.

## Kompatibilitas dan pemecahan masalah

| Masalah | Pemeriksaan |
| --- | --- |
| Menu plugin tidak muncul | Pastikan plugin diaktifkan melalui Sistem → Plugins dan folder berada di lokasi yang benar |
| Header/footer OPAC ikut tampil | Pastikan `birthday.php` versi terbaru digunakan sebagai halaman OPAC `happybirthdayto` |
| Animasi berhenti | Bersihkan cache browser; pastikan `public/effect.js` dan `public/jquery.min.js` termuat tanpa error JavaScript |
| Aset tidak ditemukan | Periksa konstanta URL dasar SLiMS (`SWB`) dan lokasi folder `plugins/daftar_ulang_tahun/public/` |
| Font diblokir CSP | Plugin menggunakan font sistem dan aset lokal; periksa pula stylesheet template OPAC jika error berasal dari luar plugin |
| Email gagal dikirim | Periksa `config('mail')`, PHPMailer/Symfony Mailer, autentikasi SMTP, serta log server |
| Musik tidak langsung berbunyi | Browser umumnya memerlukan interaksi pengguna; klik tombol **Play Music** |
|

## Lisensi dan kredit

Plugin ini mengintegrasikan aset dan animasi dari kode ucapan ulang tahun yang menjadi dasar pengembangan. **Periksa dan pertahankan informasi lisensi serta atribusi pada repositori sumber setiap aset** sebelum memublikasikan ulang gambar, audio, JavaScript, atau pustaka pihak ketiga. Lisensi plugin dan lisensi aset bawaan tidak otomatis sama.

---

**Dikembangkan untuk pengembangan layanan keanggotaan perpustakaan berbasis SLiMS.**
