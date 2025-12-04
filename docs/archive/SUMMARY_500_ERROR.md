# 500エラー根本原因と解決策まとめ

## 確認済みの項目

1. ✅ `.htaccess`の`Options`ディレクティブをコメントアウト済み
2. ✅ シンボリックリンクが正しく設定されている
3. ✅ 権限設定は問題なし（755）
4. ✅ キャッシュはクリア済み
5. ⚠️ エラーログには過去のデータベース接続エラーのみ（新しいWebアクセス時のエラーなし）

## 最も可能性が高い根本原因

**データベース接続エラー**です。

### 理由

1. Laravelの`HandleInertiaRequests`ミドルウェアで、すべてのリクエストに対して`$request->user()`を呼び出している
2. 認証チェック時にデータベースセッションを読み込もうとする
3. データベース接続に失敗すると、エラーハンドラーが動く前に500エラーが返される
4. `APP_DEBUG=false`のため、詳細なエラーが表示されない

## 解決手順

### ステップ1: 一時的にAPP_DEBUGをtrueにしてエラーを確認

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
cd ~/www/ProfileGen

# .envファイルを編集
nano .env
# または
vi .env

# APP_DEBUG=false を APP_DEBUG=true に変更
```

ブラウザで `https://navyracoon2.sakura.ne.jp` にアクセスして、**詳細なエラーメッセージを確認してください**。

### ステップ2: データベース接続を確認

```bash
cd ~/www/ProfileGen
php test-db.php
```

または：

```bash
php artisan tinker
DB::connection()->getPdo();
```

### ステップ3: データベース接続が失敗している場合

`.env`のDB設定を再確認：

```bash
cat ~/www/ProfileGen/.env | grep "^DB_"
```

正しい設定：
- `DB_HOST=mysql80.navyracoon2.sakura.ne.jp`
- `DB_DATABASE=navyracoon2_a1`
- `DB_USERNAME=navyracoon2`
- `DB_PASSWORD=12345678aa`

### ステップ4: マイグレーションを実行

データベース接続が成功したら：

```bash
cd ~/www/ProfileGen
php artisan migrate --force
```

### ステップ5: APP_DEBUGをfalseに戻す

**⚠️ 重要**: エラーを確認した後、必ず`APP_DEBUG=false`に戻してください。

```bash
# .envで APP_DEBUG=false に戻す
```

## 補足：エラーログが更新されない理由

Webアクセス時のエラーがログに記録されない理由：
- PHP Fatal Errorが発生している
- エラーハンドラーが初期化される前にエラーが発生している
- `storage/logs`への書き込み権限の問題（ただし権限は755なので問題なし）

## 確認方法

1. **ブラウザでアクセス**: `https://navyracoon2.sakura.ne.jp`
2. **test-web-access.phpで確認**: `https://navyracoon2.sakura.ne.jp/test-web-access.php`
3. **直接PHPで実行**: サーバーで`php ProfileGen/public/index.php`を実行してエラーを確認

## 次のアクション

**最も重要なステップ**: `APP_DEBUG=true`に変更して、ブラウザで詳細なエラーメッセージを確認してください。

それにより、データベース接続エラーか、それ以外のエラーかを特定できます。

