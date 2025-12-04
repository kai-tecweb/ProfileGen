# 記事機能改善のデプロイ手順

## デプロイが必要なファイル

### バックエンド
1. `app/Http/Controllers/ArticleController.php` - 画像URL抽出ロジック
2. `app/Models/Article.php` - image_urlsフィールド
3. `database/migrations/2025_11_30_062921_add_image_urls_to_articles_table.php` - マイグレーション（既に実行済み）

### フロントエンド
1. `public/build/` - ビルド済みファイル（再生成が必要）
2. `resources/js/Pages/Articles/Index.tsx` - 記事一覧画面
3. `resources/js/Pages/Articles/Edit.tsx` - 記事編集画面
4. `resources/js/Pages/Articles/Create.tsx` - 記事登録画面
5. `resources/js/Types/index.ts` - 型定義

## デプロイ手順

### 1. フロントエンドのビルド
```bash
npm run build
```

### 2. サーバーへのアップロード

以下のファイルをサーバーにアップロード：

1. **バックエンドファイル**:
   - `app/Http/Controllers/ArticleController.php`
   - `app/Models/Article.php`

2. **フロントエンドファイル**:
   - `public/build/` ディレクトリ全体

3. **キャッシュクリア**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

## 確認事項

### 改善が反映されない場合

1. **ブラウザのキャッシュをクリア**
   - Ctrl+Shift+R（Windows/Linux）
   - Cmd+Shift+R（Mac）

2. **フロントエンドのビルドファイルが最新か確認**
   - `public/build/manifest.json`のタイムスタンプを確認

3. **サーバーのファイルが更新されているか確認**
   - `app/Http/Controllers/ArticleController.php`の更新日時を確認

## 実装済みの機能

- ✅ 画像URL抽出（HTMLパース時）
- ✅ 記事一覧画面の改善（タイトル、URL、本文プレビュー、作成日時）
- ✅ 記事編集画面の改善（textareaスタイル、改行保持、折り返し）
- ✅ 記事登録画面の改善（同様のスタイル改善）

