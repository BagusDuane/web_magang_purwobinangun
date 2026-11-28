# Setup Otomatisasi Environment & Dependensi Kalurahan Purwobinangun
$ErrorActionPreference = "Continue"

Write-Host "`n=== [1/6] Memeriksa Lingkungan PHP & Composer ===" -ForegroundColor Cyan

# 1. Cek PHP
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    Write-Host "[ERROR] PHP tidak terdeteksi dalam PATH sistem!" -ForegroundColor Red
    Write-Host "Silakan install PHP / XAMPP / Laragon dan tambahkan direktori PHP ke PATH Environment Variables." -ForegroundColor Yellow
    exit 1
}
Write-Host "[OK] PHP terdeteksi: $(php -v | Select-Object -First 1)" -ForegroundColor Green

# 2. Cek Composer
$composerCmd = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composerCmd) {
    Write-Host "[ERROR] Composer tidak terdeteksi dalam PATH sistem!" -ForegroundColor Red
    Write-Host "Silakan download dan install Composer dari https://getcomposer.org/" -ForegroundColor Yellow
    exit 1
}
Write-Host "[OK] Composer terdeteksi." -ForegroundColor Green

# 3. Cek dan aktifkan ekstensi php.ini
Write-Host "`n=== [2/6] Memeriksa & Mengaktifkan Ekstensi php.ini ===" -ForegroundColor Cyan
$phpIni = (php -r "echo php_ini_loaded_file();").Trim()

if (-not $phpIni -or -not (Test-Path $phpIni)) {
    Write-Host "[WARN] File php.ini tidak terdeteksi otomatis oleh PHP CLI." -ForegroundColor Yellow
} else {
    Write-Host "Lokasi php.ini: $phpIni" -ForegroundColor Gray
    $requiredExts = @('zip', 'gd', 'fileinfo', 'pdo_mysql', 'pdo_sqlite', 'sqlite3', 'mbstring', 'curl')
    $activeExts = (php -m) | ForEach-Object { $_.Trim().ToLower() }
    
    $needsEdit = $false
    foreach ($ext in $requiredExts) {
        if ($activeExts -notcontains $ext) {
            $needsEdit = $true
            break
        }
    }

    if ($needsEdit) {
        Write-Host "Ditemukan ekstensi wajib yang belum aktif. Mencoba mengaktifkan di php.ini..." -ForegroundColor Yellow
        try {
            $iniContent = Get-Content $phpIni -Raw
            $originalContent = $iniContent
            
            # Backup php.ini
            $backupPath = "$phpIni.bak_purwobinangun"
            if (-not (Test-Path $backupPath)) {
                Copy-Item $phpIni $backupPath
                Write-Host "[OK] Backup php.ini tersimpan di: $backupPath" -ForegroundColor Green
            }

            foreach ($ext in $requiredExts) {
                # Aktifkan format ;extension=nama dan ;extension=php_nama.dll
                $pattern1 = '(?m)^;\s*extension\s*=\s*' + [regex]::Escape($ext) + '\s*$'
                $pattern2 = '(?m)^;\s*extension\s*=\s*php_' + [regex]::Escape($ext) + '\.dll\s*$'
                
                $iniContent = [regex]::Replace($iniContent, $pattern1, "extension=$ext")
                $iniContent = [regex]::Replace($iniContent, $pattern2, "extension=php_$ext.dll")
            }

            if ($iniContent -ne $originalContent) {
                Set-Content -Path $phpIni -Value $iniContent -Encoding UTF8
                Write-Host "[OK] Ekstensi berhasil diaktifkan di php.ini." -ForegroundColor Green
                Write-Host "[CATATAN] Jika Apache/Web Server sedang berjalan, silakan RESTART Apache agar ekstensi aktif di browser." -ForegroundColor Yellow
            }
        } catch {
            Write-Host "[WARN] Tidak dapat menulis langsung ke php.ini (perlu hak akses Administrator): $($_.Exception.Message)" -ForegroundColor Yellow
            Write-Host "Jalankan script ini dengan 'Run as Administrator' atau aktifkan manual di $($phpIni):" -ForegroundColor Yellow
            Write-Host "  extension=zip`n  extension=gd`n  extension=fileinfo`n  extension=pdo_mysql" -ForegroundColor Gray
        }
    } else {
        Write-Host "[OK] Semua ekstensi wajib (zip, gd, fileinfo, pdo_mysql, pdo_sqlite, sqlite3, mbstring) sudah aktif!" -ForegroundColor Green
    }
}

# 4. Memeriksa file .env & set otomatis ke SQLite
Write-Host "`n=== [3/6] Konfigurasi Environment (.env) ===" -ForegroundColor Cyan
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "[OK] File .env belum ada. Berhasil disalin dari .env.example." -ForegroundColor Green
    } else {
        Write-Host "[WARN] .env.example tidak ditemukan. Pastikan file .env dikonfigurasi manual." -ForegroundColor Yellow
    }
}

# Otomatis pastikan konfigurasi database di .env menggunakan SQLite agar plug-and-play tanpa perlu MySQL/XAMPP
if (Test-Path ".env") {
    $envContent = Get-Content ".env" -Raw
    $newEnvContent = $envContent
    
    $newEnvContent = [regex]::Replace($newEnvContent, '(?m)^DB_CONNECTION=.*$', 'DB_CONNECTION=sqlite')
    $newEnvContent = [regex]::Replace($newEnvContent, '(?m)^DB_HOST=(?!#).*$', '# DB_HOST=127.0.0.1')
    $newEnvContent = [regex]::Replace($newEnvContent, '(?m)^DB_PORT=(?!#).*$', '# DB_PORT=3306')
    $newEnvContent = [regex]::Replace($newEnvContent, '(?m)^DB_DATABASE=(?!#).*$', '# DB_DATABASE=web_magang_purwobinangun')
    $newEnvContent = [regex]::Replace($newEnvContent, '(?m)^DB_USERNAME=(?!#).*$', '# DB_USERNAME=root')
    $newEnvContent = [regex]::Replace($newEnvContent, '(?m)^DB_PASSWORD=(?!#).*$', '# DB_PASSWORD=')
    
    if ($newEnvContent -ne $envContent) {
        Set-Content -Path ".env" -Value $newEnvContent -Encoding UTF8
        Write-Host "[OK] Konfigurasi database di .env diset otomatis ke SQLite (Plug & Play)." -ForegroundColor Green
    } else {
        Write-Host "[OK] File .env sudah menggunakan SQLite (Plug & Play)." -ForegroundColor Green
    }
}

# 5. Composer Install & Vendor Check
Write-Host "`n=== [4/6] Menginstal Dependensi PHP (Composer) ===" -ForegroundColor Cyan
Write-Host "Menjalankan composer install..." -ForegroundColor Gray
composer install --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "[WARN] Mencoba composer install dengan toleransi versi PHP platform..." -ForegroundColor Yellow
    composer install --no-interaction --ignore-platform-req=php+
}
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Composer install mengalami kegagalan!" -ForegroundColor Red
    Write-Host "Jika terdapat masalah ekstensi, pastikan php.ini sudah menyimpan perubahan dan coba lagi." -ForegroundColor Yellow
    exit 1
} else {
    Write-Host "[OK] Dependensi composer berhasil diinstal." -ForegroundColor Green
}

# 6. Generate APP_KEY, Database, Storage Link & Cache
Write-Host "`n=== [5/6] Inisialisasi Kunci, Database & Storage Laravel ===" -ForegroundColor Cyan
$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch "APP_KEY=base64:") {
    Write-Host "Menghasilkan APP_KEY..." -ForegroundColor Gray
    php artisan key:generate --ansi
} else {
    Write-Host "[OK] APP_KEY sudah tersedia." -ForegroundColor Green
}

# Pastikan file database.sqlite tersedia untuk koneksi SQLite
if (-not (Test-Path "database")) {
    New-Item -ItemType Directory -Path "database" -Force | Out-Null
}
if (-not (Test-Path "database/database.sqlite")) {
    New-Item -ItemType File -Path "database/database.sqlite" -Force | Out-Null
    Write-Host "[OK] File database/database.sqlite berhasil dibuat." -ForegroundColor Green
} else {
    Write-Host "[OK] File database/database.sqlite siap digunakan." -ForegroundColor Green
}

Write-Host "Mereset dan menjalankan migrasi database lengkap (php artisan migrate:fresh --seed)..." -ForegroundColor Gray
php artisan migrate:fresh --seed --force --ansi

Write-Host "Menghubungkan storage (php artisan storage:link)..." -ForegroundColor Gray
if (-not (Test-Path "public/storage")) {
    php artisan storage:link --ansi
} else {
    Write-Host "[OK] Symlink storage sudah terhubung (public/storage)." -ForegroundColor Green
}

Write-Host "Membersihkan cache sistem..." -ForegroundColor Gray
php artisan optimize:clear --ansi

# 7. Node / NPM Assets (Opsional jika node tersedia)
Write-Host "`n=== [6/6] Memeriksa Asset Frontend (Node/NPM) ===" -ForegroundColor Cyan
$npmCmd = Get-Command npm.cmd -ErrorAction SilentlyContinue
if (-not $npmCmd) {
    $npmCmd = Get-Command npm -ErrorAction SilentlyContinue
}
if ($npmCmd) {
    $npmExe = $npmCmd.Source
    if (-not (Test-Path "node_modules")) {
        Write-Host "Menginstal dependensi npm..." -ForegroundColor Gray
        & $npmExe install
    }
    if (-not (Test-Path "public/build")) {
        Write-Host "Mengompilasi asset frontend (npm run build)..." -ForegroundColor Gray
        & $npmExe run build
    }
    Write-Host "[OK] Asset frontend siap." -ForegroundColor Green
} else {
    Write-Host "[INFO] Node/NPM tidak terdeteksi. Lewati kompilasi frontend." -ForegroundColor Gray
}

Write-Host "`n==========================================================" -ForegroundColor Green
Write-Host " SETUP SELESAI! APLIKASI PURWOBINANGUN SIAP DIJALANKAN " -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
Write-Host "Langkah berikutnya:" -ForegroundColor White
Write-Host "  1. Jalankan server: php artisan serve" -ForegroundColor Yellow
Write-Host "  2. Buka di browser: http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "`nAkun Default Siap Pakai:" -ForegroundColor White
Write-Host "  * Admin : purwo34041601@gmail.com / Password123" -ForegroundColor Gray
Write-Host "  * Warga : 3404051205900001 / password123`n" -ForegroundColor Gray
