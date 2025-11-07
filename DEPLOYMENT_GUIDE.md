# Deployment Guide ke Server

## Prerequisites Server

### 1. Install Docker dan Docker Compose
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user ke docker group (untuk menjalankan docker tanpa sudo)
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verifikasi instalasi
docker --version
docker-compose --version
```

### 2. Install Git
```bash
sudo apt install git -y
```

## Langkah-langkah Deployment

### Step 1: Clone Repository ke Server
```bash
# Clone dari GitHub
git clone https://github.com/Ahmad-Rizki21/InvoiceAlfa.git /var/www/invoice-app

# Masuk ke direktori project
cd /var/www/invoice-app

# Set permissions
sudo chown -R $USER:$USER /var/www/invoice-app
```

### Step 2: Konfigurasi Environment untuk Production
```bash
# Copy file env
cp env .env

# Edit file .env untuk production
nano .env
```

**Update di file .env:**
```env
# Ganti APP_URL ke domain atau IP server
APP_URL=http://192.168.200.20:8000

# Pastikan DB sudah sesuai
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=my_invoice
DB_USERNAME=chayuser
DB_PASSWORD=Ts0mNfNEwc4Se3g

# Update WebSocket URLs
WEBSOCKET_URL=ws://192.168.200.20:6001
WEBSOCKET_HTTP_URL=http://192.168.200.20:6001

# Production settings
APP_ENV=production
APP_DEBUG=false
```

### Step 3: Jalankan Docker Containers
```bash
# Build dan jalankan semua containers
docker-compose up -d --build

# Cek status containers
docker-compose ps

# Lihat logs jika ada error
docker-compose logs -f
```

### Step 4: Setup Database
```bash
# Jalankan migration (jika database belum ada)
docker-compose exec app php artisan migrate

# Jalankan seeder (jika ada data awal)
docker-compose exec app php artisan db:seed

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Step 5: Verifikasi Deployment
```bash
# Cek apakah aplikasi berjalan
curl http://localhost:8000

# Cek WebSocket connection
curl http://localhost:6001

# Cek semua containers status
docker-compose ps
```

## Konfigurasi Tambahan untuk Production

### 1. Setup Firewall
```bash
# Allow Nginx/HTTP traffic
sudo ufw allow 8000
sudo ufw allow 6001

# Enable firewall
sudo ufw enable
```

### 2. Setup SSL Certificate (Optional - jika ingin HTTPS)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Request SSL certificate
sudo certbot --nginx -d yourdomain.com

# Auto-renewal
sudo crontab -e
# Tambahkan: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 3. Setup Auto-restart (Systemd Service)
```bash
# Buat systemd service file
sudo nano /etc/systemd/system/invoice-app.service
```

Isi file:
```ini
[Unit]
Description=Invoice App Docker Compose
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/var/www/invoice-app
ExecStart=/usr/local/bin/docker-compose up -d
ExecStop=/usr/local/bin/docker-compose down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
```

Enable service:
```bash
sudo systemctl daemon-reload
sudo systemctl enable invoice-app.service
sudo systemctl start invoice-app.service
```

### 4. Backup Script
```bash
# Buat backup script
sudo nano /usr/local/bin/backup-invoice.sh
```

Isi file:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/invoice-app"

# Buat backup directory
mkdir -p $BACKUP_DIR

# Backup database
docker-compose exec -T mysql mysqldump -u chayuser -pTs0mNfNEwc4Se3g my_invoice > $BACKUP_DIR/db_backup_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/invoice-app

# Hapus backup lama (7 hari)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

Make executable:
```bash
sudo chmod +x /usr/local/bin/backup-invoice.sh

# Tambahkan ke crontab untuk daily backup
sudo crontab -e
# Tambahkan: 0 2 * * * /usr/local/bin/backup-invoice.sh
```

## Monitoring dan Maintenance

### 1. Monitoring Containers
```bash
# Monitor resource usage
docker stats

# Cek container health
docker-compose ps

# View logs
docker-compose logs -f --tail=100
```

### 2. Update Application
```bash
cd /var/www/invoice-app

# Pull latest changes
git pull origin main

# Rebuild containers
docker-compose build --no-cache

# Restart containers
docker-compose up -d
```

### 3. Commands Berguna
```bash
# Restart semua containers
docker-compose restart

# Restart container spesifik
docker-compose restart app
docker-compose restart nginx

# Masuk ke container
docker-compose exec app bash

# Cek disk usage
df -h

# Cek memory usage
free -h
```

## Troubleshooting

### Common Issues:

1. **Container tidak bisa start**
   ```bash
   # Cek logs
   docker-compose logs

   # Restart semua
   docker-compose down
   docker-compose up -d
   ```

2. **Database connection failed**
   ```bash
   # Cek mysql container
   docker-compose logs mysql

   # Restart mysql
   docker-compose restart mysql
   ```

3. **Permission denied**
   ```bash
   # Set proper permissions
   sudo chown -R www-data:www-data /var/www/invoice-app
   sudo chmod -R 755 /var/www/invoice-app/storage
   ```

4. **Port sudah digunakan**
   ```bash
   # Cek ports yang digunakan
   sudo netstat -tulpn | grep :8000

   # Kill process yang menggunakan port
   sudo kill -9 <PID>
   ```

## Final Checklist Sebelum Production

- [ ] Docker dan Docker Compose terinstall
- [ ] Repository berhasil di-clone
- [ ] Environment variables sudah dikonfigurasi dengan benar
- [ ] Database sudah di-setup dan migration dijalankan
- [ ] Firewall dikonfigurasi
- [ ] Aplikasi dapat diakses via browser
- [ ] WebSocket connection berfungsi
- [ ] Backup script sudah di-setup
- [ ] Monitoring sudah dikonfigurasi
- [ ] SSL certificate (jika diperlukan) sudah di-setup

## Access URLs Setelah Deployment

- **Main Application**: http://192.168.200.20:8000
- **WebSocket Server**: ws://192.168.200.20:6001
- **MySQL**: localhost:3306 (jika butuh akses dari luar container)
- **Redis**: localhost:6379 (jika butuh akses dari luar container)