# 簽到系統 (Sign-In System)

基於 Laravel + MySQL 開發的活動簽到系統，支援 Excel 匯入參與者資料、條碼掃描簽到、簽到記錄匯出等功能。

## 功能特色

### 活動管理
- ✅ 建立活動（設定活動名稱、活動日期）
- ✅ 上傳 Excel 檔案匯入參與者資料
- ✅ 自動識別中文和英文欄位名稱（身份證、姓名等）
- ✅ 保留 Excel 所有原始欄位資料
- ✅ 查看活動詳情和參與者列表
- ✅ 活動統計（總參與者數、已簽到、未簽到、簽到率）

### 條碼掃描簽到
- ✅ 瀏覽器相機掃描身份證條碼（支援手機和電腦）
- ✅ 手動輸入身份證字號簽到
- ✅ 防止重複簽到機制
- ✅ 即時顯示簽到結果

### 資料匯出
- ✅ 匯出簽到記錄 Excel 檔案
- ✅ 包含原始 Excel 所有欄位
- ✅ 包含簽到狀態（已簽到/未簽到）
- ✅ 包含簽到時間

## 系統需求

- PHP >= 8.2
- Composer
- MySQL >= 5.7 或 MariaDB >= 10.3
- Node.js 和 NPM（前端資源編譯，可選）
- 支援相機的現代瀏覽器（Chrome、Firefox、Safari、Edge）

## 安裝步驟

### 1. 安裝依賴套件

```bash
composer install
```

### 2. 環境配置

複製環境配置檔案：

```bash
cp .env.example .env
```

編輯 `.env` 檔案，設定資料庫連線：

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=signin
DB_USERNAME=root
DB_PASSWORD=
```

### 3. 產生應用程式金鑰

```bash
php artisan key:generate
```

### 4. 執行資料庫遷移

```bash
php artisan migrate
```

### 5. 設定儲存連結

```bash
php artisan storage:link
```

### 6. 啟動開發伺服器

```bash
php artisan serve
```

訪問 `http://localhost:8000` 即可使用系統。

## Excel 檔案格式

### 必要欄位

Excel 檔案第一行必須包含以下欄位之一：

**身份證欄位**（擇一）：
- `身份證`、`身分證`、`身份證號`、`身分證號`、`證號`、`證件號`
- `ID`、`id_number`、`idnumber`、`idcard`、`identity`

**姓名欄位**（擇一）：
- `姓名`、`名字`、`名稱`
- `name`

### 範例格式

| 身份證 | 姓名 | 地址 | 備註 |
|--------|------|------|------|
| L123456789 | 張大明 | 台北市 | 無 |
| A123456789 | 李小明 | 高雄市 | 無 |

**注意**：
- 第一行必須是標題行（欄位名稱）
- 系統會自動讀取所有欄位（包括身份證和姓名以外的欄位）
- 其他欄位會儲存在 `extra_data` 中，匯出時會包含

## 使用說明

### 1. 建立活動

1. 點擊「建立活動」
2. 填寫活動名稱和活動日期
3. 上傳包含參與者資料的 Excel 檔案
4. 系統會自動匯入參與者資料

### 2. 簽到

1. 在活動詳情頁面點擊「簽到頁面」
2. 點擊「開始掃描」
3. 允許瀏覽器存取相機權限
4. 將身份證條碼對準掃描框，或手動輸入身份證字號
5. 系統會自動處理簽到並顯示結果

### 3. 查看簽到記錄

1. 在活動詳情頁面可以查看所有參與者的簽到狀態
2. 查看統計資訊（總數、已簽到、未簽到、簽到率）

### 4. 匯出簽到記錄

1. 在活動詳情頁面點擊「匯出 Excel」
2. 下載的 Excel 檔案包含：
   - 原始 Excel 所有欄位
   - 簽到狀態（已簽到/未簽到）
   - 簽到時間（如已簽到）

## 技術棧

### 後端
- **Laravel 12** - PHP 框架
- **MySQL** - 資料庫
- **Laravel Excel** (maatwebsite/excel) - Excel 處理

### 前端
- **Bootstrap 5** - CSS 框架
- **Html5Qrcode.js** - 條碼掃描器

## 專案結構

```
signin/
├── app/
│   ├── Http/Controllers/
│   │   ├── EventController.php      # 活動管理
│   │   ├── CheckInController.php    # 簽到功能
│   │   └── ExportController.php     # 匯出功能
│   ├── Models/
│   │   ├── Event.php                # 活動模型
│   │   ├── Participant.php          # 參與者模型
│   │   └── CheckIn.php             # 簽到記錄模型
│   ├── Imports/
│   │   └── ParticipantImport.php    # Excel 匯入處理
│   └── Exports/
│       └── CheckInExport.php        # Excel 匯出處理
├── database/
│   └── migrations/                  # 資料庫遷移檔案
├── resources/
│   └── views/
│       ├── events/                  # 活動相關頁面
│       ├── checkin/                 # 簽到頁面
│       └── layouts/                 # 佈局檔案
└── routes/
    └── web.php                      # 路由定義
```

## 資料庫結構

### events 表（活動）
- `id` - 主鍵
- `name` - 活動名稱
- `event_date` - 活動日期
- `created_at`, `updated_at` - 時間戳記

### participants 表（參與者）
- `id` - 主鍵
- `event_id` - 活動 ID（外鍵）
- `id_number` - 身份證字號
- `name` - 姓名
- `extra_data` - 其他欄位資料（JSON）
- `created_at`, `updated_at` - 時間戳記

### check_ins 表（簽到記錄）
- `id` - 主鍵
- `participant_id` - 參與者 ID（外鍵，唯一）
- `check_in_time` - 簽到時間
- `created_at`, `updated_at` - 時間戳記

## 注意事項

### 相機權限
- 條碼掃描功能需要瀏覽器相機權限
- 建議使用 HTTPS 連線（localhost 除外）
- 手機和電腦瀏覽器都支援

### Excel 匯入
- 支援 `.xlsx` 和 `.xls` 格式
- 檔案大小限制：10MB
- 匯入成功後檔案會自動刪除
- 標題行會保留原始中文格式（不進行 slug 轉換）

### 瀏覽器相容性
- Chrome（推薦）
- Firefox
- Safari（macOS/iOS）
- Edge

## 授權

本專案採用 MIT 授權條款。
