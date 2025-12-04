# データベース接続エラー解決方法

## エラー内容

```
SQLSTATE[HY000] [1045] Access denied for user 'navyracoon2'@'xxx.xxx.xxx.xxx' (using password: YES)
```

## 解決手順

### 1. .envファイルの確認

サーバーにSSH接続して確認：

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
cd ~/www/ProfileGen
cat .env | grep "^DB_"
```

### 2. データベース接続テスト

```bash
# 直接MySQLコマンドでテスト
mysql -h mysql80.navyracoon2.sakura.ne.jp -u navyracoon2 -p12345678aa navyracoon2_a1 -e "SELECT 1;"
```

### 3. Laravel接続テスト

```bash
# キャッシュをクリア
php artisan config:clear

# 接続テスト
php test-db.php

# または
php artisan tinker
# DB::connection()->getPdo();
```

### 4. よくある原因と解決方法

#### パスワードに特殊文字が含まれている場合

`.env`ファイルでパスワードをクォートで囲む：

```env
DB_PASSWORD="12345678aa"
```

#### データベースユーザーの権限が不足している場合

さくらサーバーのコントロールパネルで：
1. データベース → MySQL
2. ユーザー一覧を確認
3. データベースへのアクセス権限を確認

#### ホスト名が間違っている場合

さくらサーバーのコントロールパネルで実際のホスト名を確認：
- `mysql80.navyracoon2.sakura.ne.jp`
- または `localhost`（ローカル接続の場合）

#### キャッシュの問題

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 5. マイグレーション実行

接続が成功したら：

```bash
php artisan migrate --force
php artisan db:seed
php artisan config:cache
```

## トラブルシューティング

### MySQL接続は成功するが、Laravel接続が失敗する場合

1. `.env`ファイルの設定を再確認
2. `php artisan config:clear`でキャッシュをクリア
3. `config/database.php`の設定を確認

### パスワードが正しく読み込まれない場合

`.env`ファイルでパスワードをクォートで囲む：

```env
DB_PASSWORD="your_password_here"
```

