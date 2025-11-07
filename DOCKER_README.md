# Docker Setup Instructions

## Prerequisites
- Docker dan Docker Compose terinstall
- Git

## Setup Instructions

### 1. Clone Repository
```bash
git clone https://github.com/Ahmad-Rizki21/InvoiceAlfa.git
cd InvoiceAlfa
```

### 2. Build dan Jalankan Container
```bash
# Jalankan semua service
docker-compose up -d

# Build ulang jika ada perubahan
docker-compose build --no-cache

# Lihat log service
docker-compose logs -f

# Log service spesifik
docker-compose logs -f nginx
docker-compose logs -f app
docker-compose logs -f websocket
```

### 3. Aplikasi dapat diakses di:
- **Main Application**: http://localhost:8000
- **WebSocket Server**: ws://localhost:6001

### 4. Service yang tersedia:
- **Nginx** (port 8000) - Web server
- **App** (PHP-FPM) - Laravel application
- **MySQL** (port 3306) - Database
- **Redis** (port 6379) - Cache & Queue
- **WebSocket** (port 6001) - WebSocket server

### 5. Commands yang berguna:
```bash
# Masuk ke container app
docker-compose exec app bash

# Jalankan migration
docker-compose exec app php artisan migrate

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Install composer packages
docker-compose exec app composer install

# Hentikan semua service
docker-compose down

# Hentikan dan hapus volume database
docker-compose down -v
```

### 6. Konfigurasi Environment:
File `.env` sudah dikonfigurasi untuk:
- **APP_URL**: http://192.168.200.20
- **Database**: my_invoice (MySQL)
- **WebSocket**: ws://192.168.200.20:6001
- **Redis**: redis:6379

### 7. Troubleshooting:
- Jika ada error permission, jalankan:
  ```bash
  sudo chown -R $USER:$USER storage/
  sudo chmod -R 755 storage/
  ```

- Jika nginx tidak dapat connect ke app:
  ```bash
  docker-compose restart nginx
  ```

- Jika database tidak terhubung:
  ```bash
  docker-compose restart mysql
  ```

### 8. Notes:
- Pastikan port 8000, 3306, 6379, dan 6001 tidak digunakan oleh aplikasi lain
- Database akan tersimpan di Docker volume `mysql_data`
- Untuk production environment, sesuaikan konfigurasi security yang diperlukan