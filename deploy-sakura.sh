#!/bin/bash
# さくらレンタルサーバー デプロイスクリプト
# 使用方法: bash deploy-sakura.sh

set -e  # エラーが発生したら終了

echo "=========================================="
echo "さくらサーバー デプロイスクリプト開始"
echo "=========================================="

# 現在のディレクトリを確認
CURRENT_DIR=$(pwd)
echo "現在のディレクトリ: $CURRENT_DIR"

# 必要なコマンドの存在確認
if ! command -v php &> /dev/null; then
    echo "エラー: php コマンドが見つかりません"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo "エラー: composer コマンドが見つかりません"
    exit 1
fi

# PHPバージョン確認
PHP_VERSION=$(php -v | head -n 1)
echo "PHPバージョン: $PHP_VERSION"

# 1. Composer依存関係のインストール
echo ""
echo "【1/7】Composer依存関係をインストール中..."
composer install --no-dev --optimize-autoloader
echo "✓ Composer依存関係のインストール完了"

# 2. .envファイルの確認
echo ""
echo "【2/7】.envファイルを確認中..."
if [ ! -f .env ]; then
    echo "警告: .envファイルが見つかりません"
    if [ -f .env.sakura.example ]; then
        echo ".env.sakura.exampleをコピーして作成してください:"
        echo "  cp .env.sakura.example .env"
        echo "その後、.envファイルを編集して設定を完了してください。"
        exit 1
    else
        echo ".envファイルを作成してください。"
        exit 1
    fi
fi
echo "✓ .envファイルが見つかりました"

# 3. アプリケーションキーの確認・生成
echo ""
echo "【3/7】アプリケーションキーを確認中..."
if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
    echo "アプリケーションキーを生成中..."
    php artisan key:generate
else
    echo "✓ アプリケーションキーは設定済みです"
fi

# 4. ストレージリンク
echo ""
echo "【4/7】ストレージリンクを作成中..."
php artisan storage:link || echo "ストレージリンクは既に存在します"
echo "✓ ストレージリンク作成完了"

# 5. データベースマイグレーション
echo ""
echo "【5/7】データベースマイグレーションを実行中..."
read -p "マイグレーションを実行しますか？ (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo "✓ マイグレーション完了"
else
    echo "マイグレーションをスキップしました"
fi

# 6. キャッシュクリア
echo ""
echo "【6/7】キャッシュをクリア中..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✓ キャッシュクリア完了"

# 7. キャッシュ設定（本番用）
echo ""
echo "【7/7】本番用キャッシュを設定中..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ 本番用キャッシュ設定完了"

# 8. 権限設定
echo ""
echo "【8/8】ファイル権限を設定中..."
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
echo "✓ ファイル権限設定完了"

echo ""
echo "=========================================="
echo "デプロイ完了！"
echo "=========================================="
echo ""
echo "次のステップ:"
echo "1. ブラウザでアクセスして動作確認"
echo "2. エラーが出る場合は、storage/logs/laravel.log を確認"
echo ""

