# ProfileGen（プロフィールメーカー）

クライアント向けのプロフィールと商品設計を自動生成する管理画面アプリケーション

## 技術スタック

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: React 18, Inertia.js, TypeScript, Tailwind CSS
- **Database**: MySQL 8.0
- **AI**: Google Gemini API

## 機能

- 記事管理（HTMLパース機能付き）
- クライアント管理
- 質問マスター管理（Gemini APIによる自動生成）
- 提案生成（Gemini APIによる自動生成）

## セットアップ

### 必要な環境

- PHP 8.2以上
- Composer
- Node.js 18以上、npm
- MySQL 8.0

### インストール手順

```bash
# 1. 依存関係のインストール
composer install
npm install --legacy-peer-deps

# 2. 環境変数の設定
cp .env.example .env
php artisan key:generate

# 3. データベース設定（.envファイルを編集）
# DB_CONNECTION=mysql
# DB_DATABASE=profilegen
# ...

# 4. マイグレーション実行
php artisan migrate

# 5. 初期ユーザー作成（オプション）
php artisan db:seed

# 6. フロントエンドビルド
npm run build
# または開発モード
npm run dev

# 7. サーバー起動
php artisan serve
```

### 開発モード

```bash
# ターミナル1: Laravelサーバー
php artisan serve

# ターミナル2: Vite開発サーバー（ホットリロード）
npm run dev
```

## 初期ログイン情報

- **メールアドレス**: `admin@example.com`
- **パスワード**: `password`

⚠️ 本番環境では必ずパスワードを変更してください。

## デプロイ

デプロイ手順については `DEPLOY.md` または `DEPLOY_QUICK.md` を参照してください。

## 設計書

詳細な設計仕様は `DESIGN.md` を参照してください。
