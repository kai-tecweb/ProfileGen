# ProfileGen（プロフィールメーカー）

クライアント向けのプロフィールと商品設計を自動生成する管理画面アプリケーション

## 技術スタック

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: React 18, Inertia.js, TypeScript, Tailwind CSS
- **Database**: MySQL 8.0
- **AI**: Google Gemini API

## 機能

- 記事管理（HTMLパース機能付き、画像URL抽出対応）
- クライアント管理（ヒアリング回答機能付き）
- 質問マスター管理（Gemini APIによる自動生成）
- 提案生成（Gemini APIによる自動生成、マルチモーダル対応）

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
# DB_USERNAME=...
# DB_PASSWORD=...
# GEMINI_API_KEY=...

# 4. マイグレーション実行
php artisan migrate

# 5. 初期ユーザー作成
php artisan db:seed
# デフォルトログイン情報:
# メール: admin@example.com
# パスワード: password

# 6. フロントエンドビルド
npm run build

# 7. 開発サーバー起動
php artisan serve
npm run dev  # 別ターミナルで
```

## ドキュメント

- `DESIGN.md` - 設計書（機能要件、データベース設計、画面設計など）
- `DEPLOY.md` - デプロイ手順
- `docs/archive/` - 過去のトラブルシューティング用ドキュメント（参照用）
