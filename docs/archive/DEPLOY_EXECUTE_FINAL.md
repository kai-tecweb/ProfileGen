# さくらサーバー最終デプロイ実行手順

## データベース接続エラー解決とデプロイ

サーバーにSSH接続して、以下のコマンドを順番に実行してください：

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
# パスワード: NPkwGwZu=NM2

cd ~/www/ProfileGen

# 1. データベース接続テスト
php test-db.php

# 2. 接続が成功したら、デプロイスクリプトを実行
/bin/sh deploy-complete.sh
```

## 手動で実行する場合

### ステップ1: データベース接続確認

```bash
cd ~/www/ProfileGen

# .envファイル確認
cat .env | grep "^DB_"

# キャッシュクリア
php artisan config:clear

# データベース接続テスト
php test-db.php
```

### ステップ2: マイグレーション実行

接続テストが成功したら：

```bash
# マイグレーション実行
php artisan migrate --force

# 初期ユーザー作成
php artisan db:seed

# キャッシュ設定
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### ステップ3: 確認

```bash
# マイグレーション状態確認
php artisan migrate:status

# テーブル確認
mysql -h mysql80.navyracoon2.sakura.ne.jp -u navyracoon2 -p12345678aa navyracoon2_a1 -e "SHOW TABLES;"
```

## データベース接続エラーが続く場合

### 確認事項

1. **さくらサーバーのコントロールパネルで確認**
   - データベース名: `navyracoon2_a1`
   - ユーザー名: `navyracoon2`
   - パスワード: `12345678aa`
   - ホスト名: `mysql80.navyracoon2.sakura.ne.jp`

2. **.envファイルの再設定**
   ```bash
   nano .env
   # 以下の値を確認・修正
   DB_HOST=mysql80.navyracoon2.sakura.ne.jp
   DB_DATABASE=navyracoon2_a1
   DB_USERNAME=navyracoon2
   DB_PASSWORD=12345678aa
   ```

3. **キャッシュクリア**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## デプロイ完了後の確認

1. **ブラウザでアクセス**
   - URL: https://navyracoon2.sakura.ne.jp

2. **ログイン**
   - メール: `admin@example.com`
   - パスワード: `password`

3. **機能確認**
   - ダッシュボード表示
   - 記事管理
   - クライアント管理
   - 質問マスター管理

