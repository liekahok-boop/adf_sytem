# 📥 CARA INSTALL DATABASE - NARAYANA HOTEL

## Metode 1: INSTALL OTOMATIS (Paling Mudah!) ⭐

1. **Buka browser, ketik:**
   ```
   http://localhost/narayana/installer.php
   ```

2. **Klik tombol "Install Database Now!"**

3. **Selesai! Database otomatis terinstall**

4. **Hapus file installer.php** setelah selesai (untuk keamanan)

---

## Metode 2: MANUAL via phpMyAdmin

### Langkah 1: Buka phpMyAdmin
- Buka browser
- Ketik: `http://localhost/phpmyadmin`
- Atau klik "Admin" di XAMPP Control Panel (bagian MySQL)

### Langkah 2: Buat Database Baru
1. Klik tab **"Databases"** di atas
2. Di kolom "Create database", ketik: `narayana_hotel`
3. Pilih "utf8mb4_unicode_ci" di dropdown collation
4. Klik tombol **"Create"**

### Langkah 3: Import File SQL

#### Cara A: Copy-Paste (Termudah)
1. Klik database **"narayana_hotel"** yang baru dibuat (di sidebar kiri)
2. Klik tab **"SQL"** di atas
3. Buka file `database.sql` dengan Notepad
4. **Copy SEMUA isi file** (Ctrl+A lalu Ctrl+C)
5. **Paste** ke kotak SQL di phpMyAdmin
6. Klik tombol **"Go"** di kanan bawah
7. Tunggu sampai muncul pesan sukses (warna hijau)

#### Cara B: Import File (Jika file size besar)
1. Klik database **"narayana_hotel"** (di sidebar kiri)
2. Klik tab **"Import"** di atas
3. Klik tombol **"Choose File"** atau **"Browse"**
4. Pilih file: `C:\xampp\htdocs\narayana\database.sql`
5. Pastikan format: **SQL**
6. Klik tombol **"Go"** di bawah
7. Tunggu proses import selesai

### Langkah 4: Verifikasi
1. Klik database **"narayana_hotel"** di sidebar kiri
2. Cek ada tabel-tabel berikut:
   - ✅ users (2 rows)
   - ✅ divisions (11 rows)
   - ✅ categories (35 rows)
   - ✅ cash_book (kosong, siap dipakai)
   - ✅ cash_balance (kosong)
   - ✅ view_division_summary
   - ✅ view_daily_summary

### Langkah 5: Login ke Aplikasi
1. Buka: `http://localhost/narayana`
2. Login dengan:
   - **Username:** `admin`
   - **Password:** `password`
3. **SELESAI!** ✅

---

## ❌ Troubleshooting

### Error: "Table already exists"
**Solusi:** Database sudah terinstall sebelumnya
1. Klik database "narayana_hotel" di sidebar
2. Klik "Drop" untuk hapus database
3. Ulangi langkah install dari awal

### Error: "Access denied"
**Solusi:** User MySQL tidak punya akses
1. Pastikan XAMPP MySQL sudah jalan (lampunya hijau)
2. Restart Apache dan MySQL di XAMPP
3. Coba lagi

### Error: "Unknown database"
**Solusi:** Database belum dibuat
1. Pastikan sudah buat database dengan nama `narayana_hotel`
2. Cek ejaan database, harus persis: `narayana_hotel` (huruf kecil semua)

### File database.sql tidak ketemu
**Solusi:**
1. Pastikan file ada di: `C:\xampp\htdocs\narayana\database.sql`
2. Atau buka file dengan Notepad, lalu copy-paste (Cara A)

---

## 🎯 Setelah Install Berhasil

### Login Credentials:
| Username | Password | Role    |
|----------|----------|---------|
| admin    | password | Admin   |
| manager  | password | Manager |

### Test Aplikasi:
1. ✅ Login berhasil
2. ✅ Dashboard muncul
3. ✅ Buka menu "Buku Kas Besar"
4. ✅ Klik "Tambah Transaksi" - test input data
5. ✅ Lihat transaksi muncul di list

---

## 📱 Akses dari HP

1. **Cek IP Address PC:**
   - Buka CMD (Command Prompt)
   - Ketik: `ipconfig`
   - Cari "IPv4 Address" (contoh: 192.168.1.5)

2. **Pastikan HP dan PC di WiFi yang sama**

3. **Buka browser HP, ketik:**
   ```
   http://192.168.1.5/narayana
   ```
   *(ganti IP sesuai IP PC Anda)*

4. **Login** dengan username `admin`, password `password`

---

## ⚡ Tips
- Gunakan **Metode 1 (Installer Otomatis)** untuk install tercepat
- Setelah install, hapus file `installer.php` untuk keamanan
- Backup database secara berkala via phpMyAdmin > Export
- Ganti password default setelah install untuk keamanan

---

**Need Help?** Check file README.md untuk dokumentasi lengkap
