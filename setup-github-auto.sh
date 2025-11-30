#!/bin/bash
# GitHub CLIを使用した自動リポジトリ作成・設定スクリプト

echo "=========================================="
echo "GitHub CLI 自動設定"
echo "=========================================="
echo ""

# GitHub CLIの確認
if ! command -v gh &> /dev/null; then
    echo "エラー: GitHub CLI (gh) がインストールされていません"
    echo "インストール方法: https://cli.github.com/"
    exit 1
fi

# GitHub CLIの認証確認
if ! gh auth status &> /dev/null; then
    echo "GitHub CLIにログインしていません"
    echo "ログイン中..."
    gh auth login
fi

echo "GitHubアカウント:"
gh api user --jq .login
echo ""

read -p "リポジトリ名 [ProfileGen]: " repo_name
repo_name=${repo_name:-ProfileGen}

read -p "説明を入力 (任意): " repo_description

read -p "プライベートリポジトリにしますか？ (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    private_flag="--private"
else
    private_flag="--public"
fi

echo ""
echo "リポジトリを作成中..."

# GitHub CLIでリポジトリを作成
if gh repo create "$repo_name" $private_flag --description "$repo_description" --source=. --remote=origin --push; then
    echo ""
    echo "=========================================="
    echo "✓ リポジトリの作成と設定が完了しました！"
    echo "=========================================="
    echo ""
    echo "リポジトリURL:"
    gh repo view --web "$repo_name" 2>/dev/null || echo "https://github.com/$(gh api user --jq .login)/${repo_name}"
    echo ""
    echo "現在のリモート設定:"
    git remote -v
else
    echo ""
    echo "⚠️  エラーが発生しました"
    echo ""
    echo "リポジトリが既に存在する場合は、手動で設定してください:"
    echo "  git remote add origin git@github.com:$(gh api user --jq .login)/${repo_name}.git"
    echo "  git push -u origin main"
    exit 1
fi

