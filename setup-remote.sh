#!/bin/bash
# リモートリポジトリ設定スクリプト

echo "=========================================="
echo "リモートリポジトリ設定"
echo "=========================================="
echo ""

# 現在のリモート設定を確認
if git remote -v | grep -q origin; then
    echo "現在のリモートリポジトリ:"
    git remote -v
    echo ""
    read -p "既存のリモートを上書きしますか？ (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git remote remove origin
    else
        echo "キャンセルしました。"
        exit 0
    fi
fi

echo "リモートリポジトリの種類を選択してください:"
echo "1. GitHub (SSH)"
echo "2. GitHub (HTTPS)"
echo "3. GitLab (SSH)"
echo "4. GitLab (HTTPS)"
echo "5. Bitbucket (SSH)"
echo "6. Bitbucket (HTTPS)"
echo "7. カスタムURLを入力"
echo ""
read -p "選択 (1-7): " choice

case $choice in
    1)
        read -p "GitHubユーザー名を入力: " username
        read -p "リポジトリ名を入力 [ProfileGen]: " repo
        repo=${repo:-ProfileGen}
        remote_url="git@github.com:${username}/${repo}.git"
        ;;
    2)
        read -p "GitHubユーザー名を入力: " username
        read -p "リポジトリ名を入力 [ProfileGen]: " repo
        repo=${repo:-ProfileGen}
        remote_url="https://github.com/${username}/${repo}.git"
        ;;
    3)
        read -p "GitLabユーザー名を入力: " username
        read -p "リポジトリ名を入力 [ProfileGen]: " repo
        repo=${repo:-ProfileGen}
        remote_url="git@gitlab.com:${username}/${repo}.git"
        ;;
    4)
        read -p "GitLabユーザー名を入力: " username
        read -p "リポジトリ名を入力 [ProfileGen]: " repo
        repo=${repo:-ProfileGen}
        remote_url="https://gitlab.com/${username}/${repo}.git"
        ;;
    5)
        read -p "Bitbucketユーザー名を入力: " username
        read -p "リポジトリ名を入力 [ProfileGen]: " repo
        repo=${repo:-ProfileGen}
        remote_url="git@bitbucket.org:${username}/${repo}.git"
        ;;
    6)
        read -p "Bitbucketユーザー名を入力: " username
        read -p "リポジトリ名を入力 [ProfileGen]: " repo
        repo=${repo:-ProfileGen}
        remote_url="https://bitbucket.org/${username}/${repo}.git"
        ;;
    7)
        read -p "リモートリポジトリURLを入力: " remote_url
        ;;
    *)
        echo "無効な選択です。"
        exit 1
        ;;
esac

echo ""
echo "以下のURLでリモートリポジトリを追加します:"
echo "$remote_url"
read -p "続行しますか？ (y/N): " -n 1 -r
echo

if [[ $REPLY =~ ^[Yy]$ ]]; then
    git remote add origin "$remote_url"
    echo "✓ リモートリポジトリを追加しました。"
    echo ""
    echo "現在のリモート設定:"
    git remote -v
    echo ""
    echo "次に、以下のコマンドでプッシュできます:"
    echo "  git push -u origin main"
else
    echo "キャンセルしました。"
fi

