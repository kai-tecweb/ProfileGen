# さくらサーバー最終デプロイ手順

## ✅ 完了した作業

- ✓ リポジトリクローン
- ✓ Composerインストール
- ✓ .envファイル作成・更新
  - DB_HOST: mysql80.navyracoon2.sakura.ne.jp
  - DB_DATABASE: navyracoon2_a1
  - DB_USERNAME: navyracoon2
  - DB_PASSWORD: 12345678aa
  - GEMINI_API_KEY: 設定済み
  - APP_URL: https://navyracoon2.sakura.ne.jp

## 🚀 最終デプロイ実行

サーバーにSSH接続して、以下のコマンドを実行してください：

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
# パスワード: NPkwGwZu=NM2

cd ~/www/ProfileGen

# 1. .envファイルを確認（必要に応じて更新）
python3 update-env-complete.py

# 2. デプロイスクリプトを実行
/bin/sh deploy-final.sh
```

## 📋 デプロイスクリプトの内容

`deploy-final.sh` は以下を実行します：

1. Composer依存関係のインストール
2. アプリケーションキー生成
3. ストレージリンク作成
4. データベースマイグレーション
5. 初期ユーザー作成
6. キャッシュ設定（config, route, view）
7. ファイル権限設定

## ✅ デプロイ後の確認

```bash
# マイグレーション状態確認
php artisan migrate:status

# データベーステーブル確認
mysql -h mysql80.navyracoon2.sakura.ne.jp -u navyracoon2 -p12345678aa navyracoon2_a1 -e "SHOW TABLES;"

# エラーログ確認（エラーが出た場合）
tail -f storage/logs/laravel.log
```

## 🌐 アクセス情報

- **URL**: https://navyracoon2.sakura.ne.jp
- **ログイン**:
  - メール: `admin@example.com`
  - パスワード: `password`

⚠️ **重要**: 本番環境では必ずパスワードを変更してください。

## 📦 フロントエンドビルド（必要に応じて）

さくらサーバーでNode.js/npmが利用可能な場合：

```bash
cd ~/www/ProfileGen
npm ci --legacy-peer-deps
npm run build
```

## 🔧 トラブルシューティング

### 500エラーが出る場合

```bash
# ログを確認
tail -f storage/logs/laravel.log

# キャッシュをクリア
php artisan optimize:clear

# 権限を確認
ls -la storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

### データベース接続エラー

- `.env`のDB設定を再確認
- データベースが起動しているか確認
- ホスト名が正しいか確認（mysql80.navyracoon2.sakura.ne.jp）

### アセットが読み込まれない

- `public/build`ディレクトリが存在するか確認
- フロントエンドをビルド: `npm run build`
- ストレージリンク: `php artisan storage:link`

