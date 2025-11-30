# さくらサーバーデプロイ最終手順

## 現在の状態

- ✓ リポジトリクローン完了
- ✓ Composerインストール済み
- ✓ .envファイル作成済み
- ⚠️ DB_HOSTを更新する必要があります

## サーバーにSSH接続して実行

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
# パスワード: NPkwGwZu=NM2

cd ~/www/ProfileGen

# 1. DB_HOSTを更新
python3 update-db-host.py
# または手動で編集
nano .env
# DB_HOST=mysql80.navyracoon2.sakura.ne.jp に変更

# 2. デプロイスクリプトを実行
/bin/sh deploy-continue.sh

# 3. マイグレーション状態確認
php artisan migrate:status

# 4. テーブル確認
mysql -h mysql80.navyracoon2.sakura.ne.jp -u navyracoon2 -p12345678aa navyracoon2_a1 -e "SHOW TABLES;"

# 5. フロントエンドビルド（Node.js設定後）
# npm ci --legacy-peer-deps
# npm run build
```

## 設定内容

- DB_HOST: mysql80.navyracoon2.sakura.ne.jp
- DB_DATABASE: navyracoon2_a1
- DB_USERNAME: navyracoon2
- DB_PASSWORD: 12345678aa
- APP_URL: https://navyracoon2.sakura.ne.jp

