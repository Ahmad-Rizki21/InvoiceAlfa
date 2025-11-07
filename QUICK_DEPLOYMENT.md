# Quick Deployment Guide

## Build Local & Deploy ke Server (Opsi Cepat)

### 1. Build di Local (Sudah Dilakukan)
```bash
# Build images
docker-compose build

# Tag untuk push ke registry
docker tag invoice-app_app:latest your-registry/invoice-app:latest
docker tag invoice-app_websocket:latest your-registry/invoice-websocket:latest
```

### 2. Push ke Docker Registry
```bash
# Push ke registry (Docker Hub, GitHub Registry, dll)
docker push your-registry/invoice-app:latest
docker push your-registry/invoice-websocket:latest
```

### 3. Update docker-compose.yml di Server
```yaml
services:
  app:
    image: your-registry/invoice-app:latest  # Ganti dari build local
    # ... konfigurasi lainnya

  websocket:
    image: your-registry/invoice-websocket:latest  # Ganti dari build local
    # ... konfigurasi lainnya
```

### 4. Deploy di Server
```bash
# Cepat, tidak perlu build lagi
git pull origin main
docker-compose up -d
```

## Build di Server (Opsi Standard)
```bash
# Standard deployment
git pull origin main
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

## Monitoring Build Process
```bash
# Monitor build progress
docker-compose logs -f

# Monitor resource usage
docker stats

# Cek container status
docker-compose ps
```

## Tips Optimasi Build
1. **Gunakan .dockerignore** untuk exclude tidak perlu
2. **Multi-stage build** untuk ukuran image lebih kecil
3. **Layer caching** dengan struktur Dockerfile yang optimal
4. **Parallel builds** dengan DOCKER_BUILDKIT=1