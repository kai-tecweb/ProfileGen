# 302エラー修正完了

## 問題の原因

質問生成（`POST /questions/generate`）で302エラーが発生していた原因：

### 1. ルート定義の順序問題

**問題**: リソースルート（`Route::resource('questions', ...)`）がカスタムルート（`/questions/generate`）より先に定義されていたため、ルートマッチングで優先順位の問題が発生する可能性がありました。

**修正**: カスタムルートをリソースルートより前に定義するように順序を変更しました。

```php
// 修正前
Route::resource('questions', QuestionController::class);
Route::post('/questions/generate', ...);  // 後から定義

// 修正後
Route::post('/questions/generate', ...);  // 先に定義
Route::resource('questions', QuestionController::class);
```

### 2. `back()` メソッドの問題

**問題**: エラー時に `back()` メソッドを使用していましたが、リファラーが存在しない場合や予期しない場所にリダイレクトする可能性がありました。

**修正**: `back()` を明示的な `redirect()->route('questions.index')` に変更しました。

```php
// 修正前
return back()->with('error', '...');

// 修正後
return redirect()->route('questions.index')
    ->with('error', '...');
```

## 修正内容

### 1. `routes/web.php`

カスタムルートをリソースルートより前に定義：

```php
// 質問マスター管理
// カスタムルートをリソースルートより前に定義（ルートマッチングの優先順位のため）
Route::post('/questions/generate', [QuestionController::class, 'generate'])->name('questions.generate');
Route::post('/questions/regenerate', [QuestionController::class, 'regenerate'])->name('questions.regenerate');
Route::resource('questions', QuestionController::class);
```

### 2. `app/Http/Controllers/QuestionController.php`

エラーハンドリングを改善：

```php
// 記事が空の場合
if ($articles->isEmpty()) {
    return redirect()->route('questions.index')
        ->with('error', '記事が登録されていません。先に記事を登録してください。');
}

// 例外発生時
catch (\Exception $e) {
    Log::error('質問生成エラー: ' . $e->getMessage());
    
    return redirect()->route('questions.index')
        ->with('error', '質問の生成に失敗しました。しばらく時間をおいて再度お試しください。');
}
```

## 確認事項

### CSRFトークン

Inertia.jsは自動的にCSRFトークンを送信するため、追加の設定は不要です。

### ミドルウェア

- `auth`: 認証必須
- `verified`: メール認証済み必須
- `HandleInertiaRequests`: Inertia.jsリクエスト処理

すべてのルートは `auth` と `verified` ミドルウェアで保護されています。

## テスト方法

1. ブラウザで「質問マスター管理」画面を開く
2. 「質問を生成」ボタンをクリック
3. 正常にリダイレクトされ、エラーメッセージまたは成功メッセージが表示されることを確認

## 期待される動作

- ✅ 正常時: `/questions` にリダイレクト（302）し、成功メッセージを表示
- ✅ エラー時: `/questions` にリダイレクト（302）し、エラーメッセージを表示
- ✅ ルートが正しくマッチする
- ✅ CSRFトークンが自動的に送信される

