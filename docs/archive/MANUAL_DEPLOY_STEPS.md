# 記事機能改善の手動デプロイ手順

## デプロイが必要なファイル

### 1. バックエンドファイル

以下のファイルをサーバーにアップロード：

```
app/Http/Controllers/ArticleController.php
app/Models/Article.php
```

### 2. フロントエンドビルドファイル

```
public/build/ ディレクトリ全体
```

## デプロイ手順

### ステップ1: バックエンドファイルをアップロード

```bash
# サーバーにSSH接続
ssh navyracoon2@navyracoon2.sakura.ne.jp

# プロジェクトディレクトリに移動
cd ~/www/ProfileGen

# ファイルをアップロード（手動でFTP/SCPを使用）
# または、ローカルから:
scp app/Http/Controllers/ArticleController.php navyracoon2@navyracoon2.sakura.ne.jp:~/www/ProfileGen/app/Http/Controllers/
scp app/Models/Article.php navyracoon2@navyracoon2.sakura.ne.jp:~/www/ProfileGen/app/Models/
```

### ステップ2: フロントエンドビルドファイルをアップロード

```bash
# ローカルでビルド（既に完了しているはず）
npm run build

# ビルドファイルをアップロード
scp -r public/build/* navyracoon2@navyracoon2.sakura.ne.jp:~/www/ProfileGen/public/build/
```

### ステップ3: サーバー側でキャッシュクリア

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
cd ~/www/ProfileGen
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### ステップ4: ブラウザで確認

1. ブラウザのキャッシュをクリア（Ctrl+Shift+R または Cmd+Shift+R）
2. 記事管理画面を確認
3. 記事編集画面を確認

## 確認項目

### 記事取り込み機能
- HTML自動抽出機能が動作しているか
- 画像URLが抽出されているか

### 記事一覧画面
- タイトル、URL、本文プレビュー、作成日時が表示されているか

### 記事編集画面
- 本文が改行を保持して表示されているか
- 読みやすいフォントになっているか

## トラブルシューティング

改善が反映されない場合：
1. ブラウザのキャッシュを完全にクリア
2. サーバーのファイルが更新されているか確認
3. フロントエンドのビルドファイルが最新か確認

