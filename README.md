# Arsitektur Voucher API

Dokumen ini menguraikan arsitektur perangkat lunak untuk Voucher API, sebuah sistem yang dirancang untuk pemrosesan data bervolume tinggi secara *non-blocking*. Arsitektur ini berfokus pada performa, skalabilitas, dan integritas data, terutama untuk menangani sejumlah besar permintaan secara bersamaan.

## Prinsip Utama Arsitektur

Tujuan utamanya adalah untuk menangani lonjakan permintaan (misalnya, pembuatan voucher) tanpa membebani server atau membuat *bottleneck*. Hal ini dicapai melalui desain *asynchronous* dan *non-blocking*.

-   **API Non-Blocking:** *Endpoint* API yang berhadapan dengan pengguna dirancang agar sangat cepat. Proses berat tidak dieksekusi secara langsung (*synchronous*). Sebaliknya, API hanya melakukan validasi, lalu mendelegasikan tugas ke antrian (*queue*) di latar belakang dan langsung memberikan respons kepada klien.
-   **Pemrosesan Asynchronous:** Proses yang sebenarnya (seperti membuat kode voucher unik dan menyimpannya ke database) ditangani oleh *background worker*. Ini memisahkan API dari tugas-tugas berat, memungkinkan sistem untuk menambah atau mengurangi jumlah *worker* secara independen sesuai dengan beban kerja.
-   **Integritas Data:** Sistem ini dibangun untuk mencegah isu-isu konkurensi seperti *race condition*, memastikan setiap voucher yang dihasilkan adalah unik dan data tetap konsisten bahkan di bawah beban kerja yang tinggi.
-   **Pemisahan Tanggung Jawab (*Separation of Concerns*):** *Codebase* disusun menggunakan *Repository Pattern* untuk memastikan pemisahan yang bersih antara logika akses data dan logika bisnis, membuat aplikasi lebih mudah dipelihara dan diuji.

## Tumpukan Teknologi (Technology Stack)

Arsitektur ini memanfaatkan serangkaian teknologi yang andal dan teruji untuk mencapai tujuannya:

-   **Backend Framework:** [Laravel](https://laravel.com/)
-   **Database:** [PostgreSQL](https://www.postgresql.org/)
-   **Queue & Cache:** [Redis](https://redis.io/)
-   **Queue Monitoring & Management:** [Laravel Horizon](https://laravel.com/docs/horizon)

## Penjelasan Mendalam Arsitektur

### 1. Sistem Antrian Asynchronous (Asynchronous Job Queuing)

Inti dari arsitektur ini adalah sistem Antrian Laravel yang didukung oleh Redis.

-   **Alur Permintaan:**
    1.  Sebuah permintaan HTTP masuk ke *endpoint* API (misalnya, `POST /api/vouchers`).
    2.  *Controller* melakukan validasi awal.
    3.  Sebuah *job* baru (misalnya, `GenerateVoucherJob`) dibuat dan dikirim ke antrian Redis.
    4.  *Controller* segera mengembalikan respons `202 Accepted` (atau sejenisnya) kepada klien.
-   **Pemrosesan oleh Worker:**
    -   Laravel Horizon mengelola sekumpulan proses *worker* yang terus-menerus memantau pekerjaan baru di antrian Redis.
    -   Ketika sebuah *job* tersedia, *worker* akan mengambil dan mengeksekusi logikanya (misalnya, membuat kode unik, melakukan operasi database).
    -   Hal ini memastikan bahwa server API hanya bertanggung jawab untuk menangani permintaan masuk dan dapat melayani jumlah yang sangat tinggi tanpa terhambat oleh operasi database yang lambat.

### 2. Antrian Berperforma Tinggi dengan Redis & Horizon

-   **Redis:** Dipilih sebagai *driver* antrian karena sifatnya yang *in-memory*, menyediakan pengiriman dan pengambilan *job* yang sangat cepat. Ini sangat ideal untuk sistem antrian dengan *throughput* tinggi.
-   **Horizon:** Menyediakan dasbor yang lengkap dan konfigurasi berbasis kode untuk antrian Redis. Ini memungkinkan pemantauan metrik utama dengan mudah seperti *throughput job*, waktu eksekusi, dan kegagalan. Horizon juga membantu dalam *auto-scaling worker*, menyeimbangkan antrian, dan mengelola *job* yang gagal.

### 3. Integritas Data & Pencegahan Race Condition

Menangani sejumlah besar *job* secara bersamaan membutuhkan strategi yang kuat untuk menjaga integritas data.

-   **Transaksi Database:** Setiap operasi yang melibatkan beberapa penulisan ke database (misalnya, membuat voucher dan memperbarui metrik terkait) dibungkus dalam transaksi database. Jika ada bagian dari operasi yang gagal, seluruh transaksi akan dibatalkan (*rolled back*), memastikan database tetap dalam keadaan konsisten.
-   **Unique Constraint:** Tabel `vouchers` memiliki *unique constraint* pada kolom `code` di tingkat database. Ini adalah jaminan utama keunikan. Kode aplikasi disiapkan untuk menangani potensi *exception* yang terjadi jika kode yang dibuat secara acak bertabrakan dengan yang sudah ada, memungkinkannya untuk mencoba kembali proses pembuatan.
-   **PostgreSQL:** Dipilih karena keandalannya yang terbukti, ketangguhannya dalam menangani penulisan bersamaan, dan fitur-fitur canggih yang mendukung integritas data pada skala besar.

### 4. Struktur Kode: Repository Pattern

Aplikasi ini menggunakan *Repository Pattern* untuk mengabstraksi lapisan data.

-   **Interfaces (`app/Interfaces`):** Mendefinisikan kontrak untuk operasi data apa yang dapat dilakukan (misalnya, `VoucherRepositoryInterface`).
-   **Repositories (`app/Repositories`):** Berisi implementasi konkret dari *interface* ini, menampung semua logika Eloquent untuk berinteraksi dengan database.
-   **Manfaat:** Ini memisahkan logika bisnis (di dalam *Service* atau *Controller*) dari logika akses data. Ini membuat kode lebih mudah diuji (Anda dapat melakukan *mocking* pada *repository interface*) dan memungkinkan perubahan mekanisme penyimpanan data di masa depan tanpa memengaruhi logika bisnis.