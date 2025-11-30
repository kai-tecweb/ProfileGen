#!/bin/bash
# 完全なデプロイスクリプト（データベース接続エラー対応版）

cd ~/www/ProfileGen

set -e

echo "=========================================="
echo "完全デプロイ開始"
echo "=========================================="
echo ""

# 1. .envファイル確認
echo "【1/9】.envファイルを確認..."
if [ ! -f .env ]; then
    echo "エラー: .envファイルが見つかりません"
    exit 1
fi
echo "✓ .envファイル確認完了"
echo ""

# 2. キャッシュをクリア
echo "【2/9】キャッシュをクリア..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✓ キャッシュクリア完了"
echo ""

# 3. データベース接続テスト
echo "【3/9】データベース接続をテスト..."
php test-db.php

if [ $? -ne 0 ]; then
    echo ""
    echo "⚠️  データベース接続テストに失敗しました"
    echo ".envファイルの設定を確認してください"
    exit 1
fi

echo "✓ データベース接続成功"
echo ""

# 4. Composer依存関係
echo "【4/9】Composer依存関係を確認..."
if [ ! -d vendor ]; then
    echo "vendorディレクトリが見つかりません。インストール中..."
    php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction
else
    echo "✓ vendorディレクトリは既に存在します"
fi
echo ""

# 5. アプリケーションキー
echo "【5/9】アプリケーションキーを確認..."
if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=base64:" .env; then
    echo "アプリケーションキーを生成中..."
    php artisan key:generate --force
else
    echo "✓ アプリケーションキーは設定済みです"
fi
echo ""

# 6. ストレージリンク
echo "【6/9】ストレージリンクを作成..."
php artisan storage:link || echo "ストレージリンクは既に存在します"
echo "✓ ストレージリンク作成完了"
echo ""

# 7. データベースマイグレーション
echo "【7/9】データベースマイグレーションを実行..."
php artisan migrate --force
echo "✓ マイグレーション完了"
echo ""

# 8. 初期ユーザー作成
echo "【8/9】初期ユーザーを作成..."
php artisan db:seed
echo "✓ 初期ユーザー作成完了"
echo ""

# 9. キャッシュ設定
echo "【9/9】キャッシュを設定..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ キャッシュ設定完了"
echo ""

# 10. 権限設定
echo "権限を設定..."
chmod -R 755 storage bootstrap/cache public 2>/dev/null || true
echo "✓ 権限設定完了"
echo ""

echo "=========================================="
echo "✓ デプロイが完了しました！"
echo "=========================================="
echo ""
echo "アクセスURL: https://navyracoon2.sakura.ne.jp"
echo "ログイン: admin@example.com / password"
echo ""

