# 500エラー→404エラー修正完了

## 実施した修正

1. ✅ `.htaccess`から`FCGIWrapper`と`AddHandler`を削除
2. ✅ `npm run build`を実行して`public/build`ディレクトリを作成
3. ✅ `public/build`ディレクトリをサーバーにアップロード（38ファイル）
4. ✅ 権限を755に設定
5. ✅ 設定キャッシュをクリア

## 現在の状況

- 500エラー → 404エラーに改善
- `public/build`ディレクトリは正しくアップロード済み
- `manifest.json`と`assets`ディレクトリも存在

## 404エラーの原因特定方法

ブラウザの開発者ツール（F12）で以下を確認：

1. **Networkタブ**を開く
2. ページを再読み込み
3. **404エラー**が出ているファイルのURLを確認
4. そのURLが正しいパスか確認

## よくある原因

### パスの問題
- ブラウザが`/build/assets/xxx.js`を要求
- 実際のファイルは`/ProfileGen/public/build/assets/xxx.js`にある
- シンボリックリンクが正しく機能していない可能性

### 解決方法
シンボリックリンクの確認：
```bash
cd ~/www
ls -la build
# または
readlink build
```

必要に応じて：
```bash
cd ~/www
ln -s ProfileGen/public/build build
```

## 確認事項

ブラウザの開発者ツール（F12）→ Networkタブで、**404エラーが出ている具体的なファイル名**を確認してください。

その情報を元に、次の修正を行います。

