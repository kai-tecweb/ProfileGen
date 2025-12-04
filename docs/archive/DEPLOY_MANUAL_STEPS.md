# さくらサーバー手動デプロイ手順

## サーバーにSSH接続して実行

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
# パスワード: NPkwGwZu=NM2

cd ~/www/ProfileGen

# 1. .envファイル確認
grep "^DB_" .env

# 2. キャッシュクリア
php artisan config:clear

# 3. データベースマイグレーション
php artisan migrate --force

# 4. 初期ユーザー作成
php artisan db:seed

# 5. キャッシュ設定
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. 確認
php artisan migrate:status
mysql -h mysql80.navyracoon2.sakura.ne.jp -u navyracoon2 -p12345678aa navyracoon2_a1 -e "SHOW TABLES;"
```

## データベース接続エラーの場合

1. さくらサーバーのコントロールパネルでデータベース情報を再確認
2. .envファイルの設定を確認
3. データベースユーザーのパスワードを再設定する場合がある

