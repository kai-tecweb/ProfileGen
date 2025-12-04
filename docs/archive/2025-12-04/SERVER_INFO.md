# 正しいサーバー情報

## サーバー環境

- **サーバー種別**: さくらのレンタルサーバー
- **ドメイン**: `navyracoon2.sakura.ne.jp`
- **URL**: `https://navyracoon2.sakura.ne.jp`
- **SSH接続**: `ssh sakura`（`~/.ssh/config`に設定済み）または直接接続 `ssh navyracoon2@navyracoon2.sakura.ne.jp`
  - **ユーザー名**: `navyracoon2`
  - **接続先**: `navyracoon2.sakura.ne.jp`
  - **ポート**: `22`
  - **初期フォルダー**: `/home/navyracoon2/`
- **プロジェクトパス**: `~/www/ProfileGen`

## SSH接続設定

### 接続コマンド
```bash
# エイリアスを使用（推奨）
ssh sakura

# または直接接続
ssh navyracoon2@navyracoon2.sakura.ne.jp

# パスワード認証を使用する場合（sshpassが必要）
sshpass -p 'パスワード' ssh navyracoon2@navyracoon2.sakura.ne.jp
```

### 接続できない場合
- さくらのレンタルサーバーのコントロールパネルでSSH接続設定を確認
- SSH接続が有効になっているか確認
- ユーザー名とパスワードを確認
- さくらのレンタルサーバーのサポートに問い合わせ

## デプロイ手順（サーバー側）

### 前提条件
- サーバーにSSH接続が可能であること
- プロジェクトディレクトリが`~/www/ProfileGen`に存在すること

### 手順

```bash
# 1. サーバーにSSH接続
ssh sakura
# または直接接続
ssh navyracoon2@navyracoon2.sakura.ne.jp

# 2. プロジェクトディレクトリに移動
cd ~/www/ProfileGen

# 3. 最新コードを取得
git pull origin main

# 4. 依存関係の更新（必要に応じて）
composer install --optimize-autoloader --no-dev
npm ci --legacy-peer-deps

# 5. フロントエンドをビルド（重要）
npm run build

# 6. マイグレーション実行（必要に応じて）
php artisan migrate --force

# 7. キャッシュクリア
php artisan config:clear
php artisan cache:clear
php artisan route:clear
composer dump-autoload

# 8. 設定キャッシュ再生成
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 次のアクション

### 1. SSH接続の確認

まず、SSH接続が可能か確認してください：

```bash
# エイリアスを使用（推奨）
ssh sakura

# または直接接続
ssh navyracoon2@navyracoon2.sakura.ne.jp

# パスワード認証を使用する場合（sshpassが必要）
sshpass -p 'パスワード' ssh navyracoon2@navyracoon2.sakura.ne.jp
```

### 2. 接続できない場合

- さくらのレンタルサーバーのコントロールパネルでSSH接続設定を確認
- SSH接続が有効になっているか確認
- ユーザー名とパスワードを確認
- さくらのレンタルサーバーのサポートに問い合わせ

### 3. 接続できた場合

上記の「デプロイ手順（サーバー側）」を実行してください。

### 4. 代替手段

SSH接続ができない場合：
- さくらのレンタルサーバーのコントロールパネルからファイルマネージャーを使用
- FTP/SFTPクライアントを使用してファイルをアップロード
- ただし、`npm run build`はサーバー側で実行する必要がある
- コードはGitHubにプッシュ済みなので、サーバーに接続できれば`git pull`で取得可能

## 参考情報

### ドキュメントの記載内容
- `SERVER_UPDATE.md`: サーバー側での更新手順
- `サーバー反映手順.txt`: サーバー側での反映手順
- `deploy-commands.txt`: さくらサーバー上で実行するコマンド
- `SSH接続問題の対処法.md`: SSH接続トラブルシューティング

### 重要
- **サーバーはさくらのレンタルサーバーです**（ConoHa VPSではありません）
- `~/.ssh/config`の`Host sakura`でさくらのレンタルサーバー（`navyracoon2.sakura.ne.jp`）に接続できます
- パスワード認証を使用する場合は`sshpass`コマンドを使用します

### プロジェクト構成
- リポジトリ: `git@github.com:kai-tecweb/ProfileGen.git`
- ブランチ: `main`
- サーバーパス: `~/www/ProfileGen`

