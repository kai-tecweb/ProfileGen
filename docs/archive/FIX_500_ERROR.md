# 500エラー修正ガイド

## 現在の状況

- `.htaccess`の`Options`ディレクティブをコメントアウト済み
- エラーログでデータベース接続エラーが確認されています
- エラー: `SQLSTATE[HY000] [1045] Access denied for user 'navyracoon2'@'112.78.112.17'`

## 確認事項

### 1. データベース接続設定の確認

サーバーで`.env`ファイルのDB設定を確認：

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
cd ~/www/ProfileGen
cat .env | grep "^DB_"
```

正しい設定：
- `DB_HOST=mysql80.navyracoon2.sakura.ne.jp`
- `DB_DATABASE=navyracoon2_a1`
- `DB_USERNAME=navyracoon2`
- `DB_PASSWORD=12345678aa`

### 2. データベース接続テスト

```bash
cd ~/www/ProfileGen
php artisan tinker
DB::connection()->getPdo();
```

または直接PHPスクリプトでテスト：

```bash
php -r "
try {
    \$pdo = new PDO('mysql:host=mysql80.navyracoon2.sakura.ne.jp;dbname=navyracoon2_a1', 'navyracoon2', '12345678aa');
    echo '接続成功';
} catch (PDOException \$e) {
    echo '接続エラー: ' . \$e->getMessage();
}
"
```

### 3. .htaccess設定の確認

`.htaccess`の`Options`ディレクティブがコメントアウトされていることを確認：

```bash
cat ~/www/.htaccess | grep -A 3 "Options"
```

### 4. Apacheエラーログの確認

さくらサーバーの場合、エラーログは通常以下の場所にあります：

```bash
tail -50 ~/logs/error_log
```

または、さくらサーバーのコントロールパネルからエラーログを確認してください。

### 5. 権限の確認

```bash
cd ~/www/ProfileGen
ls -la storage/logs/
ls -la bootstrap/cache/
```

書き込み権限があることを確認（755または775）。

## 修正手順

### ステップ1: .envファイルの完全な確認と修正

```bash
cd ~/www/ProfileGen
cat .env
```

DB設定が正しくない場合は、以下で修正：

```bash
# Pythonスクリプトで.envを更新（csh/tcsh対応）
python3 << 'EOF'
import re

env_file = '/home/navyracoon2/www/ProfileGen/.env'
with open(env_file, 'r') as f:
    content = f.read()

# DB設定を更新
content = re.sub(r'^DB_HOST=.*', 'DB_HOST=mysql80.navyracoon2.sakura.ne.jp', content, flags=re.MULTILINE)
content = re.sub(r'^DB_DATABASE=.*', 'DB_DATABASE=navyracoon2_a1', content, flags=re.MULTILINE)
content = re.sub(r'^DB_USERNAME=.*', 'DB_USERNAME=navyracoon2', content, flags=re.MULTILINE)
content = re.sub(r'^DB_PASSWORD=.*', 'DB_PASSWORD=12345678aa', content, flags=re.MULTILINE)

with open(env_file, 'w') as f:
    f.write(content)

print('✓ .envを更新しました')
EOF
```

### ステップ2: キャッシュクリア

```bash
cd ~/www/ProfileGen
php artisan config:clear
php artisan cache:clear
```

### ステップ3: データベース接続テスト

```bash
php artisan migrate:status
```

### ステップ4: 再度アクセスして確認

ブラウザでアクセスして500エラーが解消されているか確認。

## トラブルシューティング

### まだ500エラーが出る場合

1. **Laravelログを確認**:
   ```bash
   tail -100 ~/www/ProfileGen/storage/logs/laravel.log
   ```

2. **PHPエラーログを確認**:
   ```bash
   tail -50 ~/logs/error_log
   ```

3. **.htaccessの完全削除（一時的）**:
   ```bash
   mv ~/www/.htaccess ~/www/.htaccess.bak
   ```
   これでアクセスして、500エラーが解消されるか確認。
   解消される場合は、.htaccessの設定に問題があります。

4. **APP_DEBUGを一時的にtrueに**:
   ```bash
   # .envで
   APP_DEBUG=true
   ```
   ブラウザでエラー詳細を確認（本番環境では必ずfalseに戻す）。

## 確認コマンド一覧

```bash
# 1. .env確認
cat ~/www/ProfileGen/.env | grep "^DB_"

# 2. Laravelログ確認
tail -50 ~/www/ProfileGen/storage/logs/laravel.log

# 3. Apacheエラーログ確認（可能な場合）
tail -50 ~/logs/error_log

# 4. .htaccess確認
head -20 ~/www/.htaccess

# 5. 権限確認
ls -la ~/www/ProfileGen/storage/logs/
ls -la ~/www/ProfileGen/bootstrap/cache/

# 6. PHPバージョン確認
php -v

# 7. データベース接続テスト
cd ~/www/ProfileGen
php artisan tinker
# その後、DB::connection()->getPdo(); を実行
```

