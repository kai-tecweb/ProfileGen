# さくらサーバー初期ドメイン対応（シンボリックリンク設定）

## 概要

さくらサーバーの初期ドメインを使用している場合、ドキュメントルート（`~/www`）を変更できません。そのため、`ProfileGen/public`の内容を`~/www`にシンボリックリンクで配置します。

## 設定済み

以下のシンボリックリンクが作成されています：

- `~/www/index.php` → `ProfileGen/public/index.php`
- `~/www/.htaccess` → `ProfileGen/public/.htaccess`
- `~/www/.user.ini` → `ProfileGen/public/.user.ini`
- `~/www/favicon.ico` → `ProfileGen/public/favicon.ico`
- `~/www/robots.txt` → `ProfileGen/public/robots.txt`
- `~/www/storage` → `ProfileGen/public/storage`
- `~/www/build` → `ProfileGen/public/build` (ビルド後)

## ディレクトリ構造

```
~/www/
├── ProfileGen/          # プロジェクト本体
│   ├── app/
│   ├── public/          # Laravelのpublicディレクトリ
│   │   ├── index.php
│   │   ├── .htaccess
│   │   └── build/       # フロントエンドビルド成果物
│   └── ...
├── index.php           # → ProfileGen/public/index.php (シンボリックリンク)
├── .htaccess           # → ProfileGen/public/.htaccess (シンボリックリンク)
└── build/              # → ProfileGen/public/build (シンボリックリンク)
```

## 再設定方法

シンボリックリンクを再設定する場合：

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
cd ~
/bin/sh setup-symlink.sh
```

## 注意事項

1. **既存ファイルのバックアップ**: 既に`~/www`にファイルがある場合、`~/www_backup`にバックアップされます

2. **buildディレクトリ**: フロントエンドビルド後、自動的にシンボリックリンクが作成されます

3. **.htaccess**: `ProfileGen/public/.htaccess`が適用されます

4. **アクセスURL**: 
   - 初期ドメイン: `https://navyracoon2.sakura.ne.jp`
   - すべてのリクエストは`index.php`を通じて処理されます

## トラブルシューティング

### シンボリックリンクが正しく動作しない場合

```bash
cd ~/www
# シンボリックリンクを確認
ls -la | grep "^l"

# シンボリックリンクを再作成
rm index.php
ln -s ProfileGen/public/index.php index.php
```

### 404エラーが出る場合

- `.htaccess`が正しく適用されているか確認
- `index.php`のシンボリックリンクが正しいか確認
- `storage/logs/laravel.log`でエラーを確認

