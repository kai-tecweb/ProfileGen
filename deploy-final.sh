#!/bin/bash
# 最終デプロイスクリプト

set -e

cd ~/www/ProfileGen

echo "=========================================="
echo "最終デプロイ開始"
echo "=========================================="
echo ""

# 1. .envファイル確認
echo "【1/8】.envファイルを確認..."
if [ ! -f .env ]; then
    echo "エラー: .envファイルが見つかりません"
    exit 1
fi
echo "✓ .envファイル確認完了"
echo ""

# 2. Composer依存関係のインストール
echo "【2/8】Composer依存関係をインストール中..."
php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction
echo "✓ Composer依存関係インストール完了"
echo ""

# 3. アプリケーションキー生成
echo "【3/8】アプリケーションキーを生成中..."
php artisan key:generate --force
echo "✓ アプリケーションキー生成完了"
echo ""

# 4. ストレージリンク
echo "【4/8】ストレージリンクを作成中..."
php artisan storage:link || echo "ストレージリンクは既に存在します"
echo "✓ ストレージリンク作成完了"
echo ""

# 5. データベースマイグレーション
echo "【5/8】データベースマイグレーションを実行中..."
php artisan migrate --force
echo "✓ マイグレーション完了"
echo ""

# 6. 初期ユーザー作成
echo "【6/8】初期ユーザーを作成中..."
php artisan db:seed
echo "✓ 初期ユーザー作成完了"
echo ""

# 7. キャッシュ設定
echo "【7/8】キャッシュを設定中..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ キャッシュ設定完了"
echo ""

# 8. 権限設定
echo "【8/8】ファイル権限を設定中..."
chmod -R 755 storage bootstrap/cache public
echo "✓ 権限設定完了"
echo ""

echo "=========================================="
echo "✓ デプロイが完了しました！"
echo "=========================================="
echo ""
echo "次のステップ:"
echo "1. ブラウザでアクセス: https://navyracoon2.sakura.ne.jp"
echo "2. ログイン: admin@example.com / password"
echo "3. フロントエンドビルド（必要に応じて）: npm ci && npm run build"
echo ""

