# 質問生成機能の修正

## 問題の原因

エラーログから、Gemini APIのモデル名が間違っていることが判明しました：

```
models/gemini-1.5-pro is not found for API version v1beta
```

## 実施した修正

✅ `config/services.php` のデフォルトモデルを `gemini-1.5-pro` → `gemini-1.5-flash` に変更

`gemini-1.5-flash` は v1beta API で利用可能なモデルです。

## 確認結果

### 記事の登録状況
- ✅ **1件の記事が登録されています**
  - ID: 1
  - タイトル: 【第9章】コンセプトメイク
  - 内容: 3284文字
  - 作成日: 2025-11-30 06:09:58

### 質問の登録状況
- ⚠️ **質問は0件です**（まだ生成されていません）

## 次のステップ

1. ブラウザで「質問マスター管理」画面を開く
2. 「質問を生成」ボタンをクリック
3. 質問が生成されるか確認

## それでも動作しない場合

`.env`ファイルでモデル名を明示的に指定：

```env
GEMINI_MODEL=gemini-1.5-flash
```

または、利用可能なモデルを確認：

```bash
curl "https://generativelanguage.googleapis.com/v1beta/models?key=YOUR_API_KEY"
```

## エラー処理の改善

フロントエンドでエラーメッセージが表示されるように、`Questions/Index.tsx` でエラーハンドリングを確認してください。

