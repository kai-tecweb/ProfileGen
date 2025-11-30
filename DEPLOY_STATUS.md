# さくらサーバーデプロイ状況

## 完了した作業 ✅

1. ✓ リポジトリのクローン（HTTPS）
   - 場所: `~/www/ProfileGen`
   - URL: https://github.com/kai-tecweb/ProfileGen.git

2. ✓ .envファイルの作成
   - `.env.sakura.example`からコピー済み
   - 編集が必要です

3. ✓ Composerのインストール
   - 場所: `~/composer.phar`
   - 使用方法: `php ~/composer.phar`

## 次のステップ

サーバーにSSH接続して、以下のコマンドを実行してください:

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
# パスワード: NPkwGwZu=NM2

cd ~/www/ProfileGen

# 1. .envファイルを編集（データベース情報などを設定）
nano .env
# または
vi .env

# 2. Composer依存関係のインストール（時間がかかります）
php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction

# 3. アプリケーションキー生成
php artisan key:generate --force

# 4. ストレージリンク
php artisan storage:link

# 5. データベースマイグレーション（.env設定後に）
php artisan migrate --force

# 6. Node.js/npmのインストール（必要に応じて）
# さくらサーバーのコントロールパネルからNode.jsを有効化するか、
# nvm等を使用してインストール

# 7. フロントエンドビルド（Node.js/npm設定後）
npm ci --legacy-peer-deps
npm run build

# 8. キャッシュ設定
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. 権限設定
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
```

## 重要な設定項目（.env）

以下の項目を必ず設定してください:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.sakura.ne.jp

DB_CONNECTION=mysql
DB_HOST=mysql**.db.sakura.ne.jp  # さくらサーバーのDBホスト
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

GEMINI_API_KEY=your_gemini_api_key
```

## トラブルシューティング

### Composer installがタイムアウトする場合
- バックグラウンドで実行: `nohup php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction &`
- ログを確認: `tail -f nohup.out`

### Node.js/npmが見つからない場合
- さくらサーバーのコントロールパネルでNode.jsを有効化
- または、nvmを使用してインストール

