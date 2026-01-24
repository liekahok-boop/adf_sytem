# DEVELOPER BRANDING CONFIGURATION

## Logo Developer & Nama Developer

### Lokasi File Konfigurasi:
**File:** `config/config.php`

### Konstanta yang Bisa Diubah:

```php
// Nama Developer (tampil di footer sidebar)
define('DEVELOPER_NAME', 'Your Developer Name');

// Path logo developer (relatif dari root)
// Logo ini HARDCODED dan tidak bisa diganti dari menu settings
define('DEVELOPER_LOGO', 'assets/img/developer-logo.png');
```

### Cara Mengganti Logo Developer:

1. Siapkan file logo dalam format PNG/JPG/SVG
2. Ukuran rekomendasi: 100x100px (square/persegi)
3. Letakkan file di folder: `assets/img/`
4. Rename file menjadi: `developer-logo.png` (atau sesuaikan dengan nama di config)
5. Atau ganti path di `config/config.php`:
   ```php
   define('DEVELOPER_LOGO', 'assets/img/nama-logo-anda.png');
   ```

### Cara Mengganti Nama Developer:

Edit file `config/config.php` baris:
```php
define('DEVELOPER_NAME', 'Nama Developer Anda');
```

### Catatan Penting:

- Logo developer **TIDAK BISA** diganti melalui menu settings (by design untuk proteksi branding)
- Hanya admin dengan akses file server yang bisa mengubahnya
- Logo hotel dapat diubah melalui menu: **Pengaturan → Logo Hotel**
- Versi aplikasi dan tahun diambil dari konstanta `APP_VERSION` dan `APP_YEAR`

### Lokasi Tampil:

Footer sidebar menampilkan:
- Logo developer (kiri)
- Nama developer (kanan atas)
- Label "Developer" (kanan bawah)
- Versi aplikasi dan tahun (bawah)

Format tampilan:
```
[Logo] Nama Developer
       Developer
─────────────────────
Version 1.0.0 • 2026
```

### File yang Terlibat:

1. **config/config.php** - Konstanta developer
2. **includes/header.php** - Render footer sidebar
3. **assets/img/developer-logo.png** - File logo developer
4. **assets/css/style.css** - Style sidebar footer

### Tips Desain Logo:

- Background transparan (PNG)
- Format square (1:1 ratio)
- Simple & readable di ukuran kecil (24x24px)
- Kontras tinggi dengan background sidebar
- Hindari terlalu banyak detail

---

**© 2026 Narayana Hotel Management System**
