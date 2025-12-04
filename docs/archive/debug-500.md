# 500エラー詳細デバッグ

## 実施した修正

1. ✅ DB_USERNAMEをnavyracoon2_a1に修正
2. ✅ データベース接続テスト成功
3. ✅ マイグレーション完了
4. ✅ .htaccessのセキュリティヘッダーをコメントアウト
5. ✅ APP_DEBUG=trueに設定済み

## 現在の状況

- Apacheの汎用エラーページが表示されている
- Laravelのエラーログに新しいエラーが記録されていない
- PHPファイルが実行されていない可能性

## 確認が必要な項目

### 1. さくらサーバーのエラーログを確認

さくらサーバーのコントロールパネルから：
1. サーバー管理 → ログ確認
2. エラーログを確認

または、SSHで：
```bash
# エラーログの場所を確認
ls -la ~/logs/
tail -50 ~/logs/error_log
```

### 2. テストファイルで確認

ブラウザで以下にアクセス：
- `https://navyracoon2.sakura.ne.jp/test-simple.php`
- `https://navyracoon2.sakura.ne.jp/test.php` (phpinfo)

これらが動作するか確認してください。

### 3. index.phpの直接実行テスト

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
cd ~/www/ProfileGen/public
php index.php
```

### 4. .htaccessの問題を確認

一時的に.htaccessをリネームしてテスト：
```bash
cd ~/www
mv .htaccess .htaccess.bak
```

ブラウザでアクセスして、エラーが変わるか確認。

### 5. パスの確認

シンボリックリンクが正しく解決されているか：
```bash
cd ~/www
ls -la index.php
readlink index.php
cat index.php | head -5
```

