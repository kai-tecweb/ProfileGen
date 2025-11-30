# Git設定手順

## 初期設定

### 1. Gitリポジトリの初期化（既に完了）

```bash
git init
```

### 2. Gitユーザー設定（既に完了）

```bash
git config user.name "Your Name"
git config user.email "your.email@example.com"
```

**グローバル設定に変更する場合：**

```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### 3. 初回コミット

```bash
# すべてのファイルをステージング
git add .

# 初回コミット
git commit -m "Initial commit: ProfileGen setup"

# コミット履歴を確認
git log --oneline
```

## リモートリポジトリの設定

### GitHubを使用する場合

#### 1. GitHubでリポジトリを作成

1. GitHubにログイン
2. 新しいリポジトリを作成（`ProfileGen`という名前など）
3. **「Initialize this repository with a README」はチェックしない**

#### 2. リモートリポジトリを追加

```bash
# SSHを使用する場合（推奨）
git remote add origin git@github.com:your-username/ProfileGen.git

# HTTPSを使用する場合
git remote add origin https://github.com/your-username/ProfileGen.git

# リモートリポジトリを確認
git remote -v
```

#### 3. 初回プッシュ

```bash
# メインブランチ名を確認・設定
git branch -M main

# 初回プッシュ
git push -u origin main
```

### その他のGitホスティングサービス

#### GitLabの場合

```bash
git remote add origin git@gitlab.com:your-username/ProfileGen.git
git push -u origin main
```

#### Bitbucketの場合

```bash
git remote add origin git@bitbucket.org:your-username/ProfileGen.git
git push -u origin main
```

## 日常的な作業フロー

### ファイルの変更をコミット

```bash
# 変更を確認
git status

# 変更をステージング
git add .

# または特定のファイルのみ
git add path/to/file.php

# コミット
git commit -m "説明: 変更内容"

# リモートにプッシュ
git push
```

### ブランチ管理

```bash
# 新しいブランチを作成
git checkout -b feature/new-feature

# ブランチを切り替え
git checkout main

# ブランチをマージ
git checkout main
git merge feature/new-feature

# ブランチを削除
git branch -d feature/new-feature
```

## サーバーでの設定（さくらサーバー）

### 1. サーバーにSSH接続

```bash
ssh username@server.sakura.ne.jp
```

### 2. サーバーでリポジトリをクローン

```bash
# プロジェクトディレクトリへ移動
cd ~/www

# リポジトリをクローン
git clone git@github.com:your-username/ProfileGen.git
# または HTTPS の場合
git clone https://github.com/your-username/ProfileGen.git

cd ProfileGen
```

### 3. サーバーで更新を取得

```bash
# 最新のコードを取得
git pull origin main

# 依存関係を更新
composer install --no-dev --optimize-autoloader
npm ci --legacy-peer-deps
npm run build

# マイグレーション実行（必要に応じて）
php artisan migrate --force

# キャッシュを再構築
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## .gitignoreの確認

以下のファイル・ディレクトリはGitに含まれません：

- `.env` - 環境変数（秘密情報を含む）
- `vendor/` - Composer依存関係
- `node_modules/` - npm依存関係
- `public/build/` - ビルド済みアセット
- `storage/logs/*.log` - ログファイル
- `.env.sakura` - さくらサーバー用環境変数（本番用）

**含まれるファイル：**
- `.env.example` - 環境変数のテンプレート
- `.env.sakura.example` - さくらサーバー用テンプレート
- `deploy-sakura.sh` - デプロイスクリプト
- その他の設定ファイル

## トラブルシューティング

### リモートリポジトリを変更する場合

```bash
# 現在のリモートを確認
git remote -v

# リモートを削除
git remote remove origin

# 新しいリモートを追加
git remote add origin git@github.com:new-username/ProfileGen.git
```

### コミットを取り消す場合

```bash
# 最後のコミットを取り消す（変更は保持）
git reset --soft HEAD~1

# 最後のコミットと変更を完全に取り消す
git reset --hard HEAD~1
```

### コンフリクトの解決

```bash
# コンフリクトが発生した場合
git status  # コンフリクトファイルを確認

# ファイルを編集してコンフリクトを解決
# <<<<<<< HEAD から ======= までが現在のブランチ
# ======= から >>>>>>> までがマージするブランチ

# 解決後
git add .
git commit -m "Resolve merge conflict"
```

## セキュリティ注意事項

⚠️ **重要：以下の情報は絶対にGitにコミットしないでください**

- `.env`ファイル（環境変数）
- APIキーやシークレット
- データベースのパスワード
- 個人情報や機密情報

これらの情報は`.gitignore`で除外されていますが、誤ってコミットしてしまった場合は以下の手順で削除してください：

```bash
# Git履歴から完全に削除（慎重に実行）
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch .env" \
  --prune-empty --tag-name-filter cat -- --all

# リモートに強制プッシュ（注意して実行）
git push origin --force --all
```

