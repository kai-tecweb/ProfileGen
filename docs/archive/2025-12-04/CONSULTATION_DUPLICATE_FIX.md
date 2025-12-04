# 相談チャット機能 重複質問の履歴表示修正レポート

修正日時: 2025年12月4日

## 問題点

重複質問の場合、新しいレコードをDBに保存していなかったため、履歴に表示されない問題があった。

- 重複質問の場合、セッション経由で既存の回答を返していた
- ページをリロードすると、履歴に表示されなくなる可能性があった
- 既存のレコードが最新20件に含まれていない場合、履歴に表示されなかった

## 修正内容

### 修正前の問題

```php
if ($existingConsultation) {
    // 既存回答がある場合
    return redirect()->route('student.consultations.index')
        ->with('warning', '同じ質問なので同じ回答をします')
        ->with('existing_consultation', $existingConsultation);
}
```

**問題点**:
- 新しいレコードをDBに保存しない
- セッション経由で既存の回答を返す
- 履歴に表示されない可能性がある

### 修正後の改善

```php
if ($existingConsultation) {
    // 重複質問でも新しいレコードを保存（履歴に表示するため）
    $newConsultation = Consultation::create([
        'question' => $question,
        'answer' => $existingConsultation->answer,  // 既存の回答を使用
        'user_id' => null, // 学生側はuser_idなし
        'is_corrected' => $existingConsultation->is_corrected,
    ]);

    return redirect()->route('student.consultations.index')
        ->with('warning', '⚠️ この質問は過去に同じ質問があります。既存の回答を表示しています。');
}
```

**改善点**:
- ✅ 重複質問でも新しいレコードをDBに保存
- ✅ 既存の回答を使用（LLM呼び出しはスキップ）
- ✅ 警告メッセージを改善
- ✅ 履歴に確実に表示される

### フロントエンドの修正

**修正前**:
```tsx
interface ConsultationsIndexProps {
    consultations: Consultation[];
    existing_consultation?: Consultation | null;
}

// 既存の相談があった場合、リストに追加（まだ存在しない場合）
if (initialExisting) {
    const exists = initialConsultations.some(c => c.id === initialExisting.id);
    if (!exists) {
        setConsultations([initialExisting, ...initialConsultations]);
    }
}
```

**修正後**:
```tsx
interface ConsultationsIndexProps {
    consultations: Consultation[];
}

// existing_consultationの特別処理を削除（既に履歴に含まれるため不要）
```

**改善点**:
- ✅ `existing_consultation`の特別処理を削除
- ✅ 既に履歴に含まれるため、追加処理が不要

## メリット

1. **履歴に確実に表示される**
   - 重複質問でも新しいレコードをDBに保存
   - ページをリロードしても履歴に表示される

2. **警告メッセージで重複を知らせる**
   - 「⚠️ この質問は過去に同じ質問があります。既存の回答を表示しています。」と表示
   - ユーザーに重複であることを明確に伝える

3. **LLM呼び出しはスキップ（コスト削減）**
   - 既存の回答を使用するため、LLM呼び出しは不要
   - APIコストを削減

4. **is_correctedフラグも引き継ぐ**
   - 既存の回答が修正されている場合、新しいレコードにも反映

## テスト方法

### テスト1: 新しい質問を投稿

**質問**: 「ココナラで商品を初めて出品する手順は？」

**確認ポイント**:
- ✅ 履歴に表示されることを確認
- ✅ LLM呼び出しが実行されることを確認
- ✅ 新しいレコードがDBに保存されることを確認

### テスト2: 同じ質問を再度投稿

**質問**: 「ココナラで商品を初めて出品する手順は？」

**確認ポイント**:
- ✅ 警告メッセージ「⚠️ この質問は過去に同じ質問があります。既存の回答を表示しています。」が表示されることを確認
- ✅ 履歴に2つ表示されることを確認
- ✅ LLM呼び出しがスキップされることを確認（既存の回答を使用）

### テスト3: ページをリロード

**確認ポイント**:
- ✅ 両方の質問が履歴に残っていることを確認
- ✅ セッションに依存しないことを確認

## デプロイ状況

- ✅ Gitコミット・プッシュ完了
- ✅ サーバー側のコード更新完了
- ✅ フロントエンドビルド・アップロード完了
- ✅ キャッシュクリア完了

## 修正ファイル

- `app/Http/Controllers/Student/ConsultationController.php` (26-38行目、60-66行目)
- `resources/js/Pages/Student/Consultations/Index.tsx` (13-34行目)

## 関連ドキュメント

- 重複質問処理の調査レポート: `docs/archive/2025-12-04/CONSULTATION_DUPLICATE_INVESTIGATION.md`

