# Sistem Informasi Penyewaan Lapangan Mini Soccer Berbasis Web

## 📌 Deskripsi Aplikasi
Sistem Informasi Penyewaan Lapangan Mini Soccer Berbasis Web merupakan aplikasi yang dirancang untuk membantu pengelola tempat penyewaan lapangan olahraga dalam mengelola proses reservasi lapangan secara digital, terstruktur, dan efisien.

Aplikasi ini menyediakan layanan pemesanan lapangan secara online, pengelolaan data lapangan, pengelolaan produk tambahan, pengelolaan transaksi penyewaan, serta pembuatan bukti booking dalam bentuk PDF.

Sistem dikembangkan sebagai implementasi konsep pemrograman web menggunakan bahasa pemrograman PHP Native dengan database MySQL.

---

## 🎯 Tujuan Pengembangan
Aplikasi ini dibuat dengan tujuan:

- Mempermudah proses penyewaan lapangan olahraga
- Mengurangi kesalahan pencatatan manual
- Mencegah bentrok jadwal penggunaan lapangan
- Mempermudah pengelolaan transaksi penyewaan
- Menyediakan bukti transaksi digital

---

## 🛠 Teknologi yang Digunakan
- PHP Native
- MySQL Database
- Bootstrap Framework
- Session Authentication
- CRUD (Create, Read, Update, Delete)
- Konsep Master Detail Database
- PDF Generator

---

## 👤 Fitur Pengguna (User)

Pengguna dapat:

- Melihat daftar lapangan yang tersedia
- Melihat informasi lapangan
- Melakukan booking lapangan
- Mengisi data pemesan
- Mendapat nomor booking otomatis
- Mengunduh bukti booking dalam bentuk PDF
- Sistem mendeteksi bentrok jadwal secara otomatis

---

## 🛠 Fitur Administrator

Administrator dapat:

- Login ke dashboard sistem
- Mengelola data lapangan
- Mengelola data produk tambahan
- Mengelola data booking
- Mengubah status pembayaran
- Melihat detail transaksi penyewaan
- Mencetak struk pembayaran

---

## 📂 Struktur Database

Database menggunakan konsep Master Detail.

### Tabel Master
- users
- lapangan
- booking
- produk

### Tabel Detail
- booking_detail

---

## 📄 Output Sistem
Sistem menghasilkan:

- Bukti booking dalam format PDF
- Struk pembayaran transaksi penyewaan

Dokumen tersebut digunakan sebagai bukti transaksi dan verifikasi pembayaran.

---

## ⚙ Cara Instalasi dan Menjalankan Aplikasi

### 1. Clone atau Download Repository
Download project ini kemudian simpan ke dalam folder:
C:\xampp\htdocs


---

### 2. Jalankan XAMPP
Aktifkan:

- Apache
- MySQL

---

### 3. Import Database
1. Buka phpMyAdmin
2. Buat database baru
3. Import file berikut:

penyewaan_lapangan.sql


---

### 4. Konfigurasi Database
Buka file:

config/database.php


Sesuaikan konfigurasi database jika diperlukan.

---

### 5. Menjalankan Aplikasi
Buka browser kemudian akses:

http://localhost/penyewaan_lapangan


---

## 🔐 Login Administrator

Username: admin
Password admin123


---

## 🔄 Alur Sistem Booking

1. Pengguna membuka website
2. Pengguna melihat daftar lapangan
3. Pengguna memilih lapangan
4. Pengguna mengisi form booking
5. Sistem melakukan validasi jadwal
6. Sistem menyimpan data booking
7. Sistem menghasilkan bukti booking PDF

---

## 📑 Konsep Sistem yang Digunakan

### CRUD
Digunakan untuk pengelolaan data lapangan, produk, dan booking.

### Session Authentication
Digunakan untuk menjaga keamanan akses administrator.

### Master Detail Database
Digunakan untuk menyimpan transaksi booking dan produk tambahan.

---

## ⚠ Keterbatasan Sistem
Aplikasi ini masih memiliki beberapa keterbatasan, yaitu:

- Belum terintegrasi dengan payment gateway
- Belum tersedia versi mobile application
- Belum terdapat notifikasi otomatis
- Bukti booking belum terkirim otomatis melalui WhatsApp

---

## 🚀 Pengembangan Kedepan
Pengembangan sistem dapat dilakukan dengan menambahkan:

- Integrasi pembayaran online
- Notifikasi otomatis
- Pengembangan aplikasi mobile
- Sistem laporan transaksi

---

## 👨‍💻 Pengembang
Aplikasi ini dikembangkan sebagai tugas besar mata kuliah Pemrograman Web.

---

## 📌 Catatan
Aplikasi ini dibuat untuk tujuan pembelajaran dan pengembangan sistem informasi berbasis web.

