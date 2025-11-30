#!/bin/bash
# デプロイ続行スクリプト

cd ~/www/ProfileGen

echo "=== データベース接続テスト ==="
mysql -h mysql80.navyracoon2.sakura.ne.jp -u navyracoon2 -p12345678aa navyracoon2_a1 -e "SELECT 1;" || echo "接続失敗"

echo ""
echo "=== Composer依存関係のインストール ==="
php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "=== アプリケーションキー生成 ==="
php artisan key:generate --force

echo ""
echo "=== ストレージリンク作成 ==="
php artisan storage:link

echo ""
echo "=== データベースマイグレーション ==="
php artisan migrate --force

echo ""
echo "=== 初期ユーザー作成 ==="
php artisan db:seed

echo ""
echo "=== キャッシュ設定 ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=== 権限設定 ==="
chmod -R 755 storage bootstrap/cache public

echo ""
echo "=== デプロイ完了 ==="

