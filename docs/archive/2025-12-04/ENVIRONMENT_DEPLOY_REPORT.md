# 環境とデプロイ方法の確認レポート

## 現在のディレクトリ

- **パス**: `/home/iwasaki/work/ProfileGen`
- **プロジェクト**: ProfileGen（相談管理システム含む）

## APP_URL（.env）

- **ローカル**: `http://localhost`
- **本番**: `https://navyracoon2.sakura.ne.jp`（ドキュメントより推測）

## Gitリポジトリ

- **リモート**: `git@github.com:kai-tecweb/ProfileGen.git`
- **ブランチ**: `main`
- **未コミット変更**: あり
  - `resources/js/Layouts/AdminV2Layout.tsx` (修正)
  - `resources/js/Layouts/StudentLayout.tsx` (修正)
  - `resources/js/Pages/AdminV2/Consultations/Index.tsx` (修正)
  - `routes/web.php` (修正)
  - `CONSULTATION_SYSTEM_REPORT.md` (新規)

## プロジェクト構成

### バックエンド
- **フレームワーク**: Laravel 12
- **PHP**: 8.2以上
- **Composer**: 使用中（`composer.json`存在）

### フロントエンド
- **フレームワーク**: React 18 + Inertia.js
- **ビルドツール**: Vite
- **TypeScript**: 使用中
- **npm**: 使用中（`package.json`存在）

### ビルド済みファイル
- **public/build/**: 存在（`manifest.json`、`assets/`ディレクトリあり）
- **ビルドコマンド**: `npm run build`（`tsc && vite build`）

## デプロイ設定

### デプロイスクリプト
- **deploy.sh**: なし
- **deploy-commands.txt**: あり（さくらサーバー用の手動実行コマンド）
- **server-commands.sh**: あり（サーバー側でのURL抽出機能確認用）
- **サーバー反映手順.txt**: あり（手動デプロイ手順）

### CI/CD
- **.gitlab-ci.yml**: なし
- **.github/workflows/**: なし
- **自動デプロイ**: なし（手動デプロイ）

### デプロイドキュメント
- **DEPLOY.md**: あり（詳細なデプロイ手順）
- **SERVER_UPDATE.md**: あり（サーバー側での更新手順）
- **SSH接続問題の対処法.md**: あり（SSH接続トラブルシューティング）

## サーバー接続情報

### SSH設定
- **サーバー**: さくらのレンタルサーバー（`navyracoon2.sakura.ne.jp`）
- **SSHホスト名**: `sakura`（`~/.ssh/config`にエイリアス設定済み）
- **ユーザー名**: `navyracoon2`
- **サーバーディレクトリ**: `~/www/ProfileGen`
- **接続コマンド**: 
  - `ssh sakura`（エイリアス使用）
  - `ssh navyracoon2@navyracoon2.sakura.ne.jp`（直接接続）
  - `sshpass -p 'パスワード' ssh navyracoon2@navyracoon2.sakura.ne.jp`（パスワード認証）

### デプロイ設定ファイル
- **config/deploy.php**: なし
- **.env内のSSH設定**: なし（SSH_HOST、DEPLOY_HOST等の記載なし）

### サーバー環境
- **サーバー種別**: さくらレンタルサーバー（DEPLOY.mdより）
- **Webサーバー**: Apache（`.htaccess`ファイル存在）
- **PHP**: 8.2以上（推奨）

## ビルド・コンパイル

### ビルド済みファイル
- **public/build/**: 存在
  - `manifest.json`: 存在
  - `assets/`: 存在
- **ビルドコマンド**: `npm run build`
- **開発サーバー**: `npm run dev`（Vite開発サーバー）

### Inertia.js
- **ビルド済みファイル**: `public/build/`に存在
- **ビルドが必要**: はい（コード変更後は`npm run build`が必要）

## 推奨デプロイ手順

### 1. ローカルでの準備

```bash
# 1. 変更をコミット・プッシュ
git add .
git commit -m "管理画面にナレッジ管理リンク追加、修正モーダルにスクロール追加"
git push origin main

# 2. フロントエンドをビルド（本番用）
npm run build
```

### 2. サーバー側での作業

#### 方法A: SSH接続が可能な場合

```bash
# 1. サーバーにSSH接続
ssh sakura
# または直接接続
ssh navyracoon2@navyracoon2.sakura.ne.jp

# 2. プロジェクトディレクトリに移動
cd ~/www/ProfileGen

# 3. 最新コードを取得
git pull origin main

# 4. 依存関係の更新
composer install --optimize-autoloader --no-dev
npm ci --legacy-peer-deps

# 5. フロントエンドをビルド
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

#### 方法B: SSH接続ができない場合

1. **GitHubから直接ダウンロード**（または別の方法でサーバーにアクセス）
2. **さくらのレンタルサーバーのコントロールパネルからファイルマネージャーを使用**
3. **FTP/SFTPクライアントでファイルアップロード**（`public/build/`ディレクトリを含む）
4. **ただし、`npm run build`はサーバー側で実行する必要がある**

### 3. 動作確認

1. ブラウザで `https://navyracoon2.sakura.ne.jp/admin/consultations` にアクセス
2. 「ナレッジ管理」リンクが表示されることを確認
3. `/admin/articles` にアクセスできることを確認
4. 修正モーダルで回答部分がスクロール可能であることを確認

## 注意事項

### ビルドファイルの扱い
- **重要**: `public/build/`ディレクトリはGit管理に含める必要がある
- または、サーバー側で`npm run build`を実行する必要がある
- 現在、`public/build/`は存在しているが、最新の変更を反映するには再ビルドが必要

### 環境変数
- `.env`ファイルはサーバー側で個別に設定が必要
- `APP_URL`は本番環境では`https://navyracoon2.sakura.ne.jp`に設定する必要がある

### セキュリティ
- `.env`ファイルはGit管理に含めない
- `APP_DEBUG=false`に設定（本番環境）
- 初期パスワードの変更を推奨

## トラブルシューティング

### SSH接続できない場合
- `SSH接続問題の対処法.md`を参照
- さくらのレンタルサーバーのコントロールパネルでSSH接続設定を確認
- 別のネットワークから接続を試す
- さくらのレンタルサーバーのサポートに問い合わせ

### ビルドエラーが発生する場合
```bash
# node_modulesを削除して再インストール
rm -rf node_modules package-lock.json
npm install --legacy-peer-deps
npm run build
```

### アセットが読み込まれない場合
```bash
# フロントエンドを再ビルド
npm run build

# キャッシュをクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 次のステップ

1. **変更をコミット・プッシュ**
2. **フロントエンドをビルド**（`npm run build`）
3. **サーバー側で最新コードを取得**
4. **サーバー側でフロントエンドを再ビルド**（または`public/build/`をアップロード）
5. **キャッシュをクリア・再生成**
6. **動作確認**

