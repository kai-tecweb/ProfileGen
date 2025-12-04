# サーバー側でのURL抽出機能適用手順

## 問題
ローカル環境ではコードが正しく実装されていますが、サーバー側で最新のコードが反映されていない可能性があります。

## 解決手順

### 1. サーバーにSSH接続

```bash
# エイリアスを使用
ssh sakura

# または直接接続
ssh navyracoon2@navyracoon2.sakura.ne.jp

# パスワード認証を使用する場合（sshpassが必要）
sshpass -p 'パスワード' ssh navyracoon2@navyracoon2.sakura.ne.jp
```

### 2. プロジェクトディレクトリに移動

```bash
cd ~/www/ProfileGen
```

### 3. 最新コードを取得

```bash
git pull origin main
```

### 4. マイグレーション実行（未実行の場合）

```bash
php artisan migrate --force
```

### 5. キャッシュクリア

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
composer dump-autoload
```

### 6. 設定キャッシュ再生成

```bash
php artisan config:cache
php artisan route:cache
```

### 7. 動作確認

1. ブラウザで記事編集画面にアクセス
2. 「第1期：キックオフ」を編集
3. 本文に以下のいずれかの形式でURLを追加：
   - `**URL**: https://example.com`
   - `- URL: https://example.com`
   - `URL: https://example.com`（行頭）
4. 保存
5. 記事一覧画面でURLが表示されることを確認

### 8. ログ確認（問題がある場合）

```bash
tail -30 storage/logs/laravel.log
```

以下のようなログが出力されるはずです：
- `記事保存処理開始` または `記事更新処理開始`
- `URL抽出処理開始`
- `URL抽出成功（パターンX）: https://...`
- `記事保存完了` または `記事更新完了`

## 注意事項

- URL抽出パターンは大文字小文字を区別しません
- URLの前後には空白や改行があっても問題ありません
- 複数のパターンがマッチする場合は、最初にマッチしたパターンのURLが使用されます

## トラブルシューティング

### URLが抽出されない場合

1. ログを確認して、どのパターンもマッチしていないか確認
2. 本文の先頭200文字がログに出力されているので、URLの記載方法を確認
3. URLの記載が正確か確認（`URL:`の後にスペースが必須）

### エラーが発生する場合

1. マイグレーションが実行されているか確認
   ```bash
   php artisan migrate:status
   ```
2. `articles`テーブルに`url`カラムが存在するか確認
   ```bash
   php artisan tinker
   >>> Schema::hasColumn('articles', 'url')
   ```
   結果が`true`になることを確認

