# HTMLパース機能とOCR機能の修正完了

## 修正内容

### 1. 改行保持機能の実装 ✅
- HTMLからテキストを抽出する際、改行を保持するように修正
- `<br>`, `<p>`, `<div>`, `<li>`などのブロック要素の改行を正しく処理
- 連続する改行を適切に整理（最大2つまで）

### 2. 画像内テキスト抽出機能（OCR）の実装 ✅
- Gemini APIを使用して画像内のテキストを抽出
- `GeminiApiService::extractTextFromImage()` メソッドを追加
- 画像をBase64エンコードしてGemini APIに送信

### 3. 画像内テキストの適切な位置への挿入 ✅
- HTMLからテキストを抽出する際、画像タグを`[画像:インデックス]`マーカーに置き換え
- OCRで抽出したテキストを画像が出現した位置に挿入
- 画像のalt属性も考慮

## 修正したファイル

1. `app/Http/Controllers/ArticleController.php`
   - `extractContentAndImagesFromHtml()`: 改行保持と画像マーカー処理
   - `extractTextWithLineBreaks()`: 改行を保持してテキスト抽出
   - `insertImageTexts()`: 画像内テキストを適切な位置に挿入

2. `app/Services/GeminiApiService.php`
   - `extractTextFromImage()`: 画像内テキストをOCRで抽出
   - `getBaseUrl()`, `getModel()`, `getApiKey()`: アクセサメソッド追加

3. `app/Services/ImageService.php`
   - 画像のダウンロードとBase64エンコード（既存）

## デプロイ完了

- ✅ ArticleControllerをサーバーにアップロード
- ✅ GeminiApiServiceをサーバーにアップロード
- ✅ ImageServiceをサーバーにアップロード
- ✅ サーバー側キャッシュクリア

## 動作確認

以下の手順で動作を確認してください：

1. 記事登録画面で「HTMLから本文を自動抽出する」にチェック
2. HTMLソースを貼り付け
3. 本文に改行が正しく保持されているか確認
4. 画像が含まれている場合、画像内テキストが適切な位置に挿入されているか確認

