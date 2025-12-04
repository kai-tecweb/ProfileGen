# さくらレンタルサーバー デプロイ手順

## 前提条件

- さくらレンタルサーバーのアカウント
- SSHアクセス権限
- MySQLデータベース作成済み
- ドメイン設定済み

## デプロイ手順

### 1. サーバーにファイルをアップロード

#### Gitを使用する場合（推奨）

```bash
# SSHでサーバーに接続
ssh username@server.sakura.ne.jp

# プロジェクトディレクトリへ移動（例: ~/www/ProfileGen）
cd ~/www
git clone your-repository-url ProfileGen
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
- `artisan`
- `composer.json`
- `package.json`

**アップロード不要**（サーバー上で生成）：
- `vendor/` → `composer install`で生成
- `node_modules/` → `npm install`で生成
- `public/build/` → `npm run build`で生成

### 2. サーバー上での初期設定

#### 2.1 .envファイルの作成

```bash
# .env.sakura.exampleをコピー
cp .env.sakura.example .env

# .envファイルを編集
nano .env
# または
vi .env
```

以下の値を設定：

```env
APP_URL=https://your-domain.sakura.ne.jp
DB_HOST=mysql**.db.sakura.ne.jp
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
GEMINI_API_KEY=your_gemini_api_key
```

#### 2.2 デプロイスクリプトの実行

```bash
# デプロイスクリプトを実行
bash deploy-sakura.sh
```

または、手動で以下のコマンドを実行：

```bash
# 1. Composer依存関係のインストール
composer install --no-dev --optimize-autoloader

# 2. アプリケーションキー生成
php artisan key:generate

# 3. ストレージリンク
php artisan storage:link

# 4. データベースマイグレーション
php artisan migrate --force

# 5. 初期ユーザー作成（オプション）
php artisan db:seed

# 6. フロントエンドビルド
npm ci --legacy-peer-deps
npm run build

# 7. キャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 8. 本番用キャッシュ設定
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. 権限設定
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
```

### 3. さくらサーバーの設定

#### 3.1 ドキュメントルートの設定

さくらサーバーのコントロールパネルで、ドキュメントルートを以下のように設定：

```
/home/username/www/ProfileGen/public
```

#### 3.2 PHPバージョンの確認

コントロールパネルでPHP 8.2以上が選択されていることを確認してください。

`.htaccess`ファイルでPHP 8.2を指定していますが、さくらサーバーではコントロールパネルでの設定が優先される場合があります。

#### 3.3 データベースの設定確認

さくらサーバーのコントロールパネルで：

1. MySQLデータベースを作成
2. データベース名、ユーザー名、パスワードを確認
3. ホスト名（`mysql**.db.sakura.ne.jp`）を確認
4. `.env`ファイルに設定を反映

### 4. 動作確認

1. ブラウザで `https://your-domain.sakura.ne.jp` にアクセス
2. ログイン画面が表示されることを確認
3. 初期ユーザーでログイン（`admin@example.com` / `password`）
4. 各機能をテスト

⚠️ **本番環境では必ずパスワードを変更してください。**

### 5. トラブルシューティング

#### 500エラーが出る場合

```bash
# ログを確認
tail -f storage/logs/laravel.log

# 権限を確認
ls -la storage bootstrap/cache

# キャッシュをクリア
php artisan optimize:clear
```

#### データベース接続エラー

- `.env`のデータベース設定を確認
- さくらサーバーのコントロールパネルでデータベース情報を確認
- `DB_HOST`が正しい形式（`mysql**.db.sakura.ne.jp`）であることを確認

#### アセットが読み込まれない場合

```bash
# フロントエンドを再ビルド
npm run build

# ストレージリンクを再作成
php artisan storage:link
```

#### 権限エラー

```bash
# 権限を再設定
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
```

### 6. 更新手順

サーバー上のアプリケーションを更新する場合：

```bash
# サーバーにSSH接続
ssh username@server.sakura.ne.jp

# プロジェクトディレクトリへ移動
cd ~/www/ProfileGen

# Gitから最新コードを取得
git pull origin main

# 依存関係を更新
composer install --no-dev --optimize-autoloader
npm ci --legacy-peer-deps

# フロントエンドをビルド
npm run build

# マイグレーション実行
php artisan migrate --force

# キャッシュを再構築
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. バックアップ

#### データベースのバックアップ

```bash
# mysqldumpでバックアップ（さくらサーバーのコントロールパネルからも可能）
mysqldump -h mysql**.db.sakura.ne.jp -u username -p database_name > backup_$(date +%Y%m%d).sql
```

#### ファイルのバックアップ

さくらサーバーのコントロールパネルから、ファイルのバックアップ機能を使用するか、Gitで管理している場合は`git pull`で復元可能です。

### 8. セキュリティチェックリスト

- [ ] `.env`ファイルが適切に設定されている
- [ ] `APP_DEBUG=false`になっている
- [ ] `APP_ENV=production`になっている
- [ ] 初期パスワードを変更済み
- [ ] `.env`ファイルが外部からアクセスできないことを確認（`.htaccess`で保護）
- [ ] `storage/`と`bootstrap/cache/`に適切な権限が設定されている
- [ ] SSL証明書が有効（HTTPSが使用されている）

## 注意事項

- さくらサーバーでは、`.htaccess`の`AddHandler`ディレクティブが制限されている場合があります。その場合は、コントロールパネルでPHPバージョンを設定してください。
- ファイルアップロードのサイズ制限は`.user.ini`で設定していますが、さくらサーバーの制限に依存する場合があります。
- メモリ制限や実行時間制限は、`.user.ini`で設定していますが、さくらサーバーのプランによって上限があります。

