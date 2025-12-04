# ProfileGen デプロイ手順

## デプロイ前チェックリスト

### 1. 環境変数の設定

`.env`ファイルに以下を設定してください：

```env
APP_NAME=ProfileGen
APP_ENV=production
APP_KEY=base64:...（php artisan key:generateで生成）
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=profilegen
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Gemini API設定
GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL=gemini-1.5-pro
GEMINI_TIMEOUT=60
GEMINI_MAX_RETRIES=3
```

### 2. サーバーへのファイルアップロード

以下の方法でファイルをアップロード：

#### Gitを使用する場合（推奨）
```bash
# サーバー上で
git clone your-repository-url
cd ProfileGen
```

#### FTP/SFTPを使用する場合
以下のファイル・ディレクトリをアップロード：
- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/`
- `resources/`
- `routes/`
- `storage/`
- `.env`
- `artisan`
- `composer.json`
- `package.json`

**アップロード不要**（サーバー上で生成）：
- `vendor/` → `composer install`で生成
- `node_modules/` → `npm install`で生成
- `public/build/` → `npm run build`で生成

### 3. サーバー上での作業

#### 依存関係のインストール

```bash
# Composer依存関係
composer install --optimize-autoloader --no-dev

# npm依存関係（本番環境ではビルドのみ必要）
npm ci --legacy-peer-deps
npm run build
```

#### データベース設定

```bash
# マイグレーション実行
php artisan migrate --force

# （オプション）初期ユーザーを作成
php artisan db:seed
```

#### キャッシュとストレージ

```bash
# 設定キャッシュ
php artisan config:cache

# ルートキャッシュ
php artisan route:cache

# ビューキャッシュ（Bladeテンプレートがある場合）
php artisan view:cache

# ストレージリンク
php artisan storage:link
```

#### 権限設定

```bash
# storage と bootstrap/cache に書き込み権限を付与
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Webサーバー設定

#### Apache の場合

`.htaccess`ファイルが`public/`ディレクトリにあることを確認し、DocumentRootを`public/`に設定：

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/ProfileGen/public
    
    <Directory /path/to/ProfileGen/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx の場合

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/ProfileGen/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5. セキュリティ設定

- `.env`ファイルはサーバー上にのみ存在し、公開されないようにする
- `APP_DEBUG=false`に設定
- 定期的にComposer/NPMパッケージを更新
- 適切なファイアウォール設定

### 6. 動作確認

1. ブラウザで `https://your-domain.com` にアクセス
2. ログイン画面が表示されることを確認
3. 初期ユーザーでログイン（`admin@example.com` / `password`）
4. 各画面が正常に動作することを確認

### 7. 本番環境での注意事項

- **初期パスワード変更**: 本番環境では必ず初期パスワードを変更してください
- **SSL証明書**: HTTPSを使用することを強く推奨します
- **バックアップ**: データベースの定期バックアップを設定してください
- **ログ監視**: `storage/logs/laravel.log`を定期的に確認してください

## トラブルシューティング

### 500エラーが出る場合

```bash
# ログを確認
tail -f storage/logs/laravel.log

# 権限を確認
ls -la storage bootstrap/cache

# キャッシュをクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### アセットが読み込まれない場合

```bash
# フロントエンドを再ビルド
npm run build

# ストレージリンクを再作成
php artisan storage:link
```

### データベース接続エラー

- `.env`のデータベース設定を確認
- データベースサーバーが起動していることを確認
- 接続権限を確認

## 更新手順

サーバー上のアプリケーションを更新する場合：

```bash
# Gitを使用する場合
git pull origin main

# 依存関係を更新
composer install --optimize-autoloader --no-dev
npm ci --legacy-peer-deps

# マイグレーション実行
php artisan migrate --force

# フロントエンドをビルド
npm run build

# ビルドファイルをサーバーにアップロード（ローカルから）
rsync -avz --delete public/build/ navyracoon2@navyracoon2.sakura.ne.jp:www/ProfileGen/public/build/

# キャッシュをクリア（サーバー上で実行）
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# または、キャッシュを再構築（本番環境推奨）
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 最終更新日

- **2025年12月4日**: ドキュメント整理、相談チャット機能の4段階構造実装完了

