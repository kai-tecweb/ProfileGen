#!/bin/bash
# さくらサーバー初期ドメイン用シンボリックリンク設定スクリプト

set -e

cd ~

echo "=========================================="
echo "シンボリックリンク設定"
echo "=========================================="
echo ""

# 1. 既存のwwwディレクトリの内容を確認
echo "【1/5】既存のwwwディレクトリを確認中..."
if [ -d www ]; then
    echo "既存のwwwディレクトリの内容:"
    ls -la www | head -10
    echo ""
    
    # ProfileGen以外のファイルがあるか確認
    COUNT=$(ls -A www 2>/dev/null | grep -v "ProfileGen" | grep -v "^\.$" | grep -v "^\.\.$" | wc -l)
    
    if [ "$COUNT" -gt 0 ]; then
        echo "⚠️  wwwディレクトリに他のファイル/ディレクトリがあります"
        echo "バックアップを作成します..."
        
        # バックアップディレクトリを作成
        if [ ! -d www_backup ]; then
            mkdir www_backup
            echo "✓ バックアップディレクトリを作成しました"
        fi
        
        # ProfileGen以外をバックアップ
        cd www
        for item in *; do
            if [ "$item" != "ProfileGen" ] && [ -e "$item" ]; then
                mv "$item" ../www_backup/ 2>/dev/null || echo "既にバックアップ済み: $item"
            fi
        done
        cd ..
        echo "✓ バックアップ完了"
    fi
else
    echo "wwwディレクトリが存在しません。作成します..."
    mkdir -p www
fi

# 2. ProfileGenディレクトリの確認
echo ""
echo "【2/5】ProfileGenディレクトリを確認中..."
if [ ! -d www/ProfileGen ]; then
    echo "エラー: www/ProfileGen が見つかりません"
    exit 1
fi

if [ ! -d www/ProfileGen/public ]; then
    echo "エラー: www/ProfileGen/public が見つかりません"
    exit 1
fi

echo "✓ ProfileGen/public ディレクトリが見つかりました"

# 3. 既存のシンボリックリンクを削除（存在する場合）
echo ""
echo "【3/5】既存のシンボリックリンクを確認中..."
cd www

# public以外のファイル/ディレクトリを確認
for item in *; do
    if [ "$item" != "ProfileGen" ] && [ -e "$item" ]; then
        if [ -L "$item" ]; then
            echo "既存のシンボリックリンクを削除: $item"
            rm "$item"
        fi
    fi
done

# 4. ProfileGen/public の内容を www にシンボリックリンクで配置
echo ""
echo "【4/5】シンボリックリンクを作成中..."

# ProfileGen/public内の各アイテムをwwwにシンボリックリンク
cd ProfileGen/public

for item in * .*; do
    # . と .. をスキップ
    if [ "$item" = "." ] || [ "$item" = ".." ]; then
        continue
    fi
    
    # 既に存在する場合はスキップ（バックアップ済みのもの）
    if [ ! -e "../../$item" ]; then
        # シンボリックリンクを作成
        ln -s "ProfileGen/public/$item" "../../$item"
        echo "✓ シンボリックリンク作成: $item -> ProfileGen/public/$item"
    else
        echo "スキップ（既存）: $item"
    fi
done

cd ../..

# 5. .htaccessの確認と設定
echo ""
echo "【5/5】.htaccessファイルを確認中..."
if [ -L .htaccess ] || [ -f .htaccess ]; then
    echo "✓ .htaccessファイルが存在します"
else
    echo "⚠️  .htaccessファイルが見つかりません"
    if [ -f ProfileGen/public/.htaccess ]; then
        ln -s ProfileGen/public/.htaccess .htaccess
        echo "✓ .htaccessのシンボリックリンクを作成しました"
    fi
fi

# 6. index.phpの確認
if [ -L index.php ] || [ -f index.php ]; then
    echo "✓ index.phpが存在します"
else
    echo "⚠️  index.phpが見つかりません"
    if [ -f ProfileGen/public/index.php ]; then
        ln -s ProfileGen/public/index.php index.php
        echo "✓ index.phpのシンボリックリンクを作成しました"
    fi
fi

cd ~

echo ""
echo "=========================================="
echo "✓ シンボリックリンク設定完了"
echo "=========================================="
echo ""
echo "現在のwwwディレクトリの構造:"
cd www
ls -la | head -15
echo ""
echo "シンボリックリンクの確認:"
ls -la | grep "^l" | head -10

