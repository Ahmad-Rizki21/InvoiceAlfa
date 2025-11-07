# Server Deployment - Quick Steps

## 1. Setup Server (192.168.200.20)

### Install Docker (jika belum):
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

## 2. Deploy Application
```bash
# Clone repository
git clone https://github.com/Ahmad-Rizki21/InvoiceAlfa.git /var/www/invoice-app
cd /var/www/invoice-app

# Copy environment file
cp env .env

# Build dan jalankan (akan butuh 15-25 menit pertama kali)
docker-compose up -d --build

# Atau gunakan build kit untuk lebih cepat
DOCKER_BUILDKIT=1 docker-compose up -d --build
```

## 3. Setup Database
```bash
# Wait 30 detik untuk MySQL start
sleep 30

# Run migration
docker-compose exec app php artisan migrate

# Clear cache
docker-compose exec app php artisan cache:clear
```

## 4. Verify Deployment
```bash
# Cek container status
docker-compose ps

# Test aplikasi
curl http://localhost:8000

# Cek logs
docker-compose logs -f
```

## 5. Access Application
- **Main App**: http://192.168.200.20:8000
- **WebSocket**: ws://192.168.200.20:6001

## Expected Build Times:
- **First time**: 15-25 menit
- **With cache**: 5-10 menit
- **Rebuild**: 2-5 menit

## Troubleshooting:
```bash
# Jika ada error
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Monitor resources
docker stats
htop
```