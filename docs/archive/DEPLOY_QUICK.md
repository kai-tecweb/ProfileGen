# ProfileGen デプロイ手順（簡易版）

## サーバーへデプロイする手順

### ステップ1: ファイルのアップロード

**Gitを使用する場合（推奨）:**
```bash
# サーバー上で
git clone your-repository-url
cd ProfileGen
```

**FTP/SFTPを使用する場合:**
- プロジェクト全体をアップロード（`.git/`、`node_modules/`、`vendor/`は除く）

### ステップ2: サーバー上で実行するコマンド

```bash
# 1. Composer依存関係のインストール
composer install --optimize-autoloader --no-dev

# 2. npm依存関係のインストールとビルド
npm ci --legacy-peer-deps
npm run build

# 3. .envファイルを作成・設定
cp .env.example .env
# .envファイルを編集して必要な設定を入力

# 4. アプリケーションキーの生成
php artisan key:generate

# 5. データベースマイグレーション
php artisan migrate --force

# 6. 初期ユーザー作成（オプション）
php artisan db:seed

# 7. ストレージリンク
php artisan storage:link

# 8. キャッシュ設定
php artisan config:cache
php artisan route:cache

# 9. 権限設定
chmod -R 775 storage bootstrap/cache
# chown -R www-data:www-data storage bootstrap/cache  # 必要に応じて
```

### ステップ3: .envファイルの設定

以下の設定を必ず確認・設定してください：

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=profilegen
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

GEMINI_API_KEY=your_gemini_api_key
```

### ステップ4: Webサーバー設定

**DocumentRootを`public/`ディレクトリに設定してください。**

- Apache: DocumentRoot → `/path/to/ProfileGen/public`
- Nginx: root → `/path/to/ProfileGen/public`

### ステップ5: 動作確認

1. ブラウザで `https://your-domain.com` にアクセス
2. ログイン画面が表示されることを確認
3. ログインして各機能をテスト

## トラブルシューティング

**500エラーが出る場合:**
```bash
tail -f storage/logs/laravel.log  # エラーログを確認
php artisan optimize:clear        # キャッシュをクリア
```

**アセットが読み込まれない場合:**
```bash
npm run build  # フロントエンドを再ビルド
```

詳細は`DEPLOY.md`を参照してください。

