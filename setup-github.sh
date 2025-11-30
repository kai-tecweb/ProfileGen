#!/bin/bash
# GitHubリモートリポジトリ設定スクリプト（簡易版）

echo "=========================================="
echo "GitHubリモートリポジトリ設定"
echo "=========================================="
echo ""

# 現在のリモート設定を確認
if git remote -v | grep -q origin; then
    echo "既存のリモートリポジトリ:"
    git remote -v
    echo ""
    read -p "既存のリモートを上書きしますか？ (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git remote remove origin
        echo "✓ 既存のリモートを削除しました"
    else
        echo "キャンセルしました。"
        exit 0
    fi
fi

echo "GitHubのリポジトリ情報を入力してください:"
echo ""

read -p "GitHubユーザー名: " github_username
if [ -z "$github_username" ]; then
    echo "エラー: ユーザー名が入力されていません"
    exit 1
fi

read -p "リポジトリ名 [ProfileGen]: " repo_name
repo_name=${repo_name:-ProfileGen}

echo ""
echo "接続方法を選択してください:"
echo "1. SSH (推奨 - パスワード不要)"
echo "2. HTTPS (認証が必要)"
read -p "選択 (1/2) [1]: " protocol
protocol=${protocol:-1}

if [ "$protocol" = "1" ]; then
    remote_url="git@github.com:${github_username}/${repo_name}.git"
else
    remote_url="https://github.com/${github_username}/${repo_name}.git"
fi

echo ""
echo "以下のURLでリモートリポジトリを追加します:"
echo "$remote_url"
echo ""
read -p "続行しますか？ (y/N): " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "キャンセルしました。"
    exit 0
fi

# リモートリポジトリを追加
git remote add origin "$remote_url"

if [ $? -eq 0 ]; then
    echo "✓ リモートリポジトリを追加しました"
    echo ""
    echo "現在のリモート設定:"
    git remote -v
    echo ""
    echo "=========================================="
    echo "次のステップ:"
    echo "=========================================="
    echo ""
    echo "1. GitHubでリポジトリを作成していることを確認"
    echo "2. 以下のコマンドでプッシュ:"
    echo "   git push -u origin main"
    echo ""
    
    read -p "今すぐプッシュしますか？ (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo ""
        echo "プッシュを実行中..."
        git push -u origin main
        if [ $? -eq 0 ]; then
            echo ""
            echo "✓ プッシュが完了しました！"
        else
            echo ""
            echo "⚠️  プッシュに失敗しました。"
            echo "   GitHubでリポジトリを作成しているか確認してください。"
        fi
    fi
else
    echo "エラー: リモートリポジトリの追加に失敗しました"
    exit 1
fi

