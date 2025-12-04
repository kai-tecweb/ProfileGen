# 500エラー根本原因分析

## 確認した項目

✅ **シンボリックリンク**: 正しく設定されている
- `~/www/index.php` → `ProfileGen/public/index.php`
- `~/www/.htaccess` → `ProfileGen/public/.htaccess`

✅ **権限**: 問題なし（755）

✅ **キャッシュ**: クリア済み

⚠️ **エラーログ**: 新しいWebアクセス時のエラーが記録されていない

## 根本原因の仮説

### 仮説1: データベース接続エラーがWebアクセス時に発生
- `HandleInertiaRequests`ミドルウェアで`$request->user()`を呼び出し
- 認証チェック時にデータベース接続を試みる
- データベース接続エラーが発生し、エラーハンドラーが動く前に500エラーが返される

### 仮説2: パスの問題
- シンボリックリンクは相対パス
- `__DIR__`が期待したパスを返していない可能性

### 仮説3: vendor/autoload.phpの読み込み失敗
- Composerのautoloaderが見つからない
- PHP Fatal Errorが発生し、ログに記録されない

## 推奨される確認手順

### 1. 一時的にAPP_DEBUG=trueにして詳細エラーを確認

```bash
cd ~/www/ProfileGen
# .envを編集
# APP_DEBUG=true に変更
```

ブラウザでアクセスして、詳細なエラーメッセージを確認。

### 2. test-web-access.phpでエラーを確認

ブラウザで `https://navyracoon2.sakura.ne.jp/test-web-access.php` にアクセス。

### 3. データベース接続を確認

```bash
cd ~/www/ProfileGen
php test-db.php
```

### 4. シンボリックリンクを絶対パスに変更（一時的）

```bash
cd ~/www
rm index.php
ln -s /home/navyracoon2/www/ProfileGen/public/index.php index.php
```

## 最も可能性が高い原因

**データベース接続エラー**です。

理由：
1. エラーログに過去のデータベース接続エラーが記録されている
2. `HandleInertiaRequests`で認証チェック時にDB接続が発生する
3. `APP_DEBUG=false`のため、エラーの詳細が表示されない

## 次のステップ

1. `APP_DEBUG=true`に一時的に変更して、ブラウザでエラーを確認
2. エラーがデータベース接続関連なら、`.env`の設定を再確認
3. データベース接続が成功することを確認してから、`APP_DEBUG=false`に戻す

