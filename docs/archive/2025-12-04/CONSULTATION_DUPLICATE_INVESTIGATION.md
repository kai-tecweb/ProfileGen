# 相談チャット機能 重複質問処理の調査レポート

調査日時: 2025年12月4日

## 1. 重複チェックの実装

### 1.1 実装箇所

**ファイル**: `app/Http/Controllers/Student/ConsultationController.php`

### 1.2 重複チェックの方法

```php
// 質問文の正規化（小文字、空白除去）して完全一致チェック
$normalizedQuestion = $this->normalizeQuestion($question);

// 既存の同じ質問を検索（正規化した質問文で完全一致チェック）
$existingConsultation = Consultation::whereRaw('LOWER(TRIM(question)) = ?', [strtolower($normalizedQuestion)])
    ->orderBy('created_at', 'desc')
    ->first();
```

**正規化メソッド**:
```php
private function normalizeQuestion(string $question): string
{
    // 空白を除去、小文字に変換
    return strtolower(trim($question));
}
```

### 1.3 重複が見つかった場合の処理

```php
if ($existingConsultation) {
    // 既存回答がある場合
    return redirect()->route('student.consultations.index')
        ->with('warning', '同じ質問なので同じ回答をします')
        ->with('existing_consultation', $existingConsultation);
}
```

**処理内容**:
- ✅ 新しいレコードをDBに保存**しない**
- ✅ LLM呼び出し**しない**
- ✅ 既存の回答をセッション経由で返す
- ✅ 警告メッセージを表示

## 2. 履歴表示の実装

### 2.1 実装箇所

**ファイル**: `app/Http/Controllers/Student/ConsultationController.php` (index()メソッド)

### 2.2 履歴取得の方法

```php
public function index(): Response
{
    $consultations = Consultation::orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

    // セッションから既存の相談を取得（既存質問の場合）
    $existingConsultation = session('existing_consultation');

    return Inertia::render('Student/Consultations/Index', [
        'consultations' => $consultations,
        'existing_consultation' => $existingConsultation,
    ]);
}
```

**取得内容**:
- 最新20件の相談履歴（`created_at`降順）
- セッションから既存の相談（重複質問の場合）

## 3. 現在の動作まとめ

### ケース1: 新しい質問を投稿

**処理フロー**:
1. 質問文を正規化して重複チェック
2. 既存の質問が見つからない
3. LLM呼び出し: ✅ **実行される**
4. DB保存: ✅ **新しいレコードが保存される**
5. 履歴表示: ✅ **最新20件に含まれる**

**結果**:
- 新しいレコードが`consultations`テーブルに保存される
- 履歴に表示される

### ケース2: 同じ質問を再度投稿

**処理フロー**:
1. 質問文を正規化して重複チェック
2. 既存の質問が見つかる
3. LLM呼び出し: ❌ **実行されない**
4. DB保存: ❌ **新しいレコードは保存されない**
5. 履歴表示: ⚠️ **既存のレコードが表示される（セッション経由で追加表示される可能性）**

**結果**:
- 新しいレコードは保存されない
- 既存の回答をセッション経由で返す
- 警告メッセージ「同じ質問なので同じ回答をします」を表示

## 4. 問題点の指摘

### 4.1 潜在的な問題

#### 問題1: 履歴に表示されない可能性

**現状**:
- 重複質問の場合、新しいレコードをDBに保存しない
- 既存の回答をセッション経由で返す
- フロントエンドで`existing_consultation`をどう処理しているか確認が必要

**懸念点**:
- セッションがクリアされると、重複質問の履歴が表示されなくなる可能性
- 既存のレコードが最新20件に含まれていない場合、履歴に表示されない

#### 問題2: 重複チェックの精度

**現状**:
- `LOWER(TRIM(question))`で完全一致チェック
- 空白や大文字小文字の違いは無視される

**懸念点**:
- 句読点の違い（「？」と「?」）は検出されない
- 全角半角の違いは検出されない
- 意味的には同じだが表記が異なる質問は重複として検出されない

#### 問題3: ユーザー体験

**現状**:
- 重複質問の場合、警告メッセージを表示
- 既存の回答を返す

**改善の余地**:
- 重複質問でも履歴に表示されるようにする
- 重複質問の回数を記録する
- 重複質問の統計を取る

### 4.2 改善提案

#### 提案1: 重複質問でも履歴に表示

**方法**:
- 重複質問の場合でも、新しいレコードをDBに保存する（ただし、LLM呼び出しはしない）
- `is_duplicate`フラグを追加して、重複質問であることを記録

**メリット**:
- 履歴に確実に表示される
- 重複質問の統計が取れる

#### 提案2: 重複チェックの精度向上

**方法**:
- より高度な正規化（句読点除去、全角半角統一など）
- 類似度チェック（Levenshtein距離など）

**メリット**:
- より正確な重複検出

#### 提案3: 重複質問の統計

**方法**:
- `duplicate_count`カラムを追加
- 重複質問の回数を記録

**メリット**:
- よくある質問を把握できる
- ナレッジの改善に活用できる

## 5. フロントエンドの確認が必要

フロントエンド（`resources/js/Pages/Student/Consultations/Index.tsx`）で`existing_consultation`をどう処理しているか確認が必要です。

**確認項目**:
- `existing_consultation`が履歴に追加表示されているか
- セッションがクリアされた後も履歴に表示されるか
- 警告メッセージが適切に表示されているか

## 6. まとめ

### 現在の実装

- ✅ 重複チェックは実装されている
- ✅ 重複質問の場合、LLM呼び出しをスキップして既存の回答を返す
- ⚠️ 重複質問の場合、新しいレコードをDBに保存しない
- ⚠️ 履歴に表示されない可能性がある

### 推奨される改善

1. **重複質問でも履歴に表示**: 新しいレコードを保存（LLM呼び出しはしない）
2. **重複チェックの精度向上**: より高度な正規化
3. **重複質問の統計**: よくある質問を把握

