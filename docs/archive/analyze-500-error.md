# 500エラー根本原因分析と解決策

## 現状確認

1. **エラーログの状況**
   - Laravelログには過去（05:34, 05:36）のデータベース接続エラーのみ
   - Webアクセス時の新しいエラーが記録されていない
   - キャッシュはクリア済み

2. **考えられる原因**

### 原因1: データベース接続エラー（Webアクセス時）
- `APP_DEBUG=false`のため、詳細なエラーが表示されない
- エラーハンドラーがエラーをログに記録する前に500エラーが返される可能性

### 原因2: ストレージ/キャッシュの権限問題
- `storage/logs`が書き込み不可
- `bootstrap/cache`が書き込み不可

### 原因3: シンボリックリンクの問題
- シンボリックリンクが正しく設定されていない
- `index.php`のパスが正しくない

### 原因4: .htaccessの問題
- `Options`ディレクティブは既にコメントアウト済み
- 他の設定に問題がある可能性

## 解決手順

### ステップ1: 一時的にAPP_DEBUGをtrueにして詳細エラーを確認

サーバーで実行：

```bash
cd ~/www/ProfileGen
# .envのAPP_DEBUGを一時的にtrueに変更
sed -i '' 's/APP_DEBUG=false/APP_DEBUG=true/' .env
# または手動で編集
```

ブラウザで再度アクセスして、詳細なエラーメッセージを確認。

**⚠️ 確認後は必ずfalseに戻すこと**

### ステップ2: 権限の確認と修正

```bash
cd ~/www/ProfileGen
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
```

### ステップ3: シンボリックリンクの確認

```bash
cd ~/www
ls -la | grep ProfileGen
ls -la index.php
```

`index.php`が`ProfileGen/public/index.php`へのシンボリックリンクであることを確認。

### ステップ4: 直接PHPスクリプトでエラーテスト

`test-web-access.php`をブラウザでアクセスしてエラーを確認：
- URL: `https://navyracoon2.sakura.ne.jp/test-web-access.php`

### ステップ5: データベース接続の最終確認

```bash
cd ~/www/ProfileGen
# test-db.phpを実行
php test-db.php
```

## 緊急対応（500エラーを回避する一時的な方法）

### 方法1: .htaccessを一時的に削除

```bash
cd ~/www
mv .htaccess .htaccess.bak
```

これでアクセスして、500エラーが解消されるか確認。
解消される場合は、.htaccessの設定に問題があります。

### 方法2: index.phpを直接確認

```bash
cd ~/www
head -20 index.php
```

Laravelの`index.php`が正しく設定されているか確認。

### 方法3: PHPエラーログの確認

さくらサーバーのコントロールパネルから：
1. サーバー管理 → ログ確認
2. エラーログを確認

または：

```bash
# エラーログの場所を確認
php -i | grep error_log
```

## 確認すべきファイル

1. `~/www/.htaccess` - 正しく更新されているか
2. `~/www/index.php` - シンボリックリンクが正しいか
3. `~/www/ProfileGen/.env` - DB設定が正しいか
4. `~/www/ProfileGen/storage/logs/` - 書き込み可能か
5. `~/www/ProfileGen/bootstrap/cache/` - 書き込み可能か

