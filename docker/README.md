# Docker 配置說明

本目錄包含 Laravel 簽到系統的 Docker Compose 配置，使用 PHP 8.4、SQLite 和 Caddy，針對低記憶體環境（1GB RAM）進行優化。

## 檔案結構

```
docker/
├── docker-compose.yml      # Docker Compose 主配置
├── env.docker.example      # 環境變數範例檔案
├── php/
│   ├── Dockerfile          # PHP 8.4 映像檔定義
│   ├── php.ini             # PHP 配置檔案
│   └── php-fpm.conf        # PHP-FPM 配置檔案
├── caddy/
│   └── Caddyfile           # Caddy 反向代理配置
└── README.md               # 本說明文件
```

## 快速開始

### 1. 準備環境變數

複製環境變數範例檔案到專案根目錄：

```bash
cp docker/.env.docker .env
```

編輯 `.env` 檔案，設定以下重要變數：
- `APP_KEY` - 執行 `php artisan key:generate` 生成
- `APP_URL` - 您的實際域名（例如：`https://signin.example.com`）
- `DB_CONNECTION` - 設為 `sqlite`（已預設）
- `DB_DATABASE` - SQLite 資料庫路徑（已預設為 `/var/www/html/database/database.sqlite`）

### 2. 更新 Caddyfile 域名

編輯 `docker/caddy/Caddyfile`，將 `signin.example.com` 替換為您的實際域名：

```
your-actual-domain.com {
    # ... 其他配置保持不變
}
```

### 3. 建置和啟動服務

```bash
# 進入 docker 目錄
cd docker

# 建置並啟動所有服務
docker-compose up -d --build

# 查看服務狀態
docker-compose ps

# 查看日誌
docker-compose logs -f
```

### 4. 初始化 Laravel 應用程式

```bash
# 進入 PHP 容器
docker-compose exec php sh

# 在容器內執行以下命令
composer install --optimize-autoloader --no-dev

# 創建 SQLite 資料庫檔案（如果不存在）
touch database/database.sqlite
chmod 664 database/database.sqlite

# 產生應用程式金鑰
php artisan key:generate

# 執行資料庫遷移
php artisan migrate --force

# 建立儲存連結
php artisan storage:link

# 快取設定
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. 設定檔案權限

```bash
# 設定 storage、bootstrap/cache 和 database 目錄權限
docker-compose exec php sh -c "chmod -R 775 storage bootstrap/cache database && chown -R www-data:www-data storage bootstrap/cache database"
```

## 服務說明

### PHP 8.4 (signin_php)
- **端口**: 9000 (內部)
- **記憶體限制**: 256MB
- **擴充功能**: pdo_sqlite, sqlite3, gd, zip, exif, intl, opcache
- **工作目錄**: `/var/www/html`

### SQLite
- **資料庫檔案**: `/var/www/html/database/database.sqlite`
- **優勢**: 無需獨立服務，記憶體需求極低，適合小型應用
- **資料持久化**: 資料庫檔案直接保存在專案目錄中

### Caddy (signin_caddy)
- **端口**: 80 (HTTP), 443 (HTTPS)
- **記憶體限制**: 128MB
- **功能**: 自動 HTTPS、反向代理、靜態檔案服務
- **域名**: 需在 `Caddyfile` 中設定

## 常用指令

### 啟動服務
```bash
docker-compose up -d
```

### 停止服務
```bash
docker-compose down
```

### 停止並刪除 volumes（⚠️ 會刪除資料庫資料）
```bash
docker-compose down -v
```

### 查看日誌
```bash
# 所有服務
docker-compose logs -f

# 特定服務
docker-compose logs -f php
docker-compose logs -f mysql
docker-compose logs -f caddy
```

### 進入容器
```bash
# PHP 容器
docker-compose exec php sh

# MySQL 容器
docker-compose exec mysql mysql -u signin_user -p signin
```

### 重建服務
```bash
# 重建 PHP 映像檔
docker-compose build php

# 重建並重啟
docker-compose up -d --build php
```

### 執行 Artisan 命令
```bash
docker-compose exec php php artisan migrate
docker-compose exec php php artisan cache:clear
```

## 資源使用

針對低記憶體環境（1GB RAM）的資源限制：

- **PHP**: 256MB (limit) / 128MB (reservation)
- **SQLite**: 無需額外記憶體（內嵌在 PHP 進程中）
- **Caddy**: 128MB (limit) / 64MB (reservation)
- **總計**: 約 384MB，適合 1GB RAM 的環境

## HTTPS 自動配置

Caddy 會自動從 Let's Encrypt 獲取 SSL 憑證，需要：
1. 域名已正確指向伺服器 IP
2. 80 和 443 端口已開放
3. `Caddyfile` 中的域名設定正確

首次啟動時，Caddy 會自動申請憑證，可能需要幾分鐘時間。

## 資料持久化

- **MySQL 資料**: 保存在 `mysql-data` volume，不會因容器刪除而遺失
- **Caddy 憑證和配置**: 保存在 `caddy-data` 和 `caddy-config` volumes

## 疑難排解

### Caddy 無法獲取 SSL 憑證
- 確認域名 DNS 已正確指向伺服器
- 確認 80 和 443 端口已開放
- 檢查 `Caddyfile` 中的域名設定

### SQLite 資料庫錯誤
- 確認 `database/database.sqlite` 檔案存在且有寫入權限
- 檢查資料庫檔案權限：`docker-compose exec php ls -la database/`
- 確認 `.env` 中 `DB_CONNECTION=sqlite` 和 `DB_DATABASE` 路徑正確
- 如果資料庫檔案不存在，執行：`touch database/database.sqlite && chmod 664 database/database.sqlite`

### PHP 錯誤
- 檢查 PHP 日誌：`docker-compose exec php cat /var/log/php_errors.log`
- 確認檔案權限已正確設定
- 確認 `storage` 和 `bootstrap/cache` 目錄可寫入

### 靜態檔案無法載入
- 確認 `public` 目錄已正確掛載到 Caddy 容器
- 檢查 Caddy 日誌：`docker-compose logs caddy`

## 生產環境建議

1. **安全性**：
   - 使用強密碼（`DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`）
   - 設定 `APP_DEBUG=false`
   - 定期更新映像檔

2. **備份**：
   - 定期備份 `database/database.sqlite` 檔案
   - 備份 `.env` 和應用程式程式碼
   - 備份 `storage/app` 目錄（上傳的檔案）

3. **監控**：
   - 監控容器資源使用情況
   - 設定日誌輪轉和清理策略

4. **性能**：
   - 根據實際需求調整 PHP-FPM 進程數
   - 調整 MySQL `innodb_buffer_pool_size`
   - 使用 Redis 作為快取和 Session 儲存（可選）

## 更新說明

### 更新域名

1. 編輯 `docker/caddy/Caddyfile`，修改域名
2. 編輯 `.env`，修改 `APP_URL`
3. 重啟 Caddy：`docker-compose restart caddy`

### 更新 PHP 版本

1. 編輯 `docker/php/Dockerfile`，修改基礎映像檔版本
2. 重建：`docker-compose build php`
3. 重啟：`docker-compose up -d php`

## 授權

本配置遵循專案 MIT 授權條款。

