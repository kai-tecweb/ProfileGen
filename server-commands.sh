#!/bin/bash
# サーバー側で実行するコマンド

echo "========================================="
echo "サーバー側でのURL抽出機能確認・適用"
echo "========================================="

cd ~/www/ProfileGen || exit 1

echo ""
echo "1. 最新コードを取得..."
git pull origin main

echo ""
echo "2. マイグレーション実行（未実行の場合）..."
php artisan migrate --force

echo ""
echo "3. キャッシュクリア..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
composer dump-autoload

echo ""
echo "4. 設定キャッシュ再生成..."
php artisan config:cache
php artisan route:cache

echo ""
echo "========================================="
echo "完了！"
echo "========================================="
echo ""
echo "次に、記事「第1期：キックオフ」を編集して、"
echo "以下の形式でURLを追加してください："
echo ""
echo "  **URL**: https://example.com"
echo "  または"
echo "  - URL: https://example.com"
echo "  または"
echo "  URL: https://example.com"
echo ""
echo "保存後、ログを確認:"
echo "  tail -30 storage/logs/laravel.log"
echo ""

