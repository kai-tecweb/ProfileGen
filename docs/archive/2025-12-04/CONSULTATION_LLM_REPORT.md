# 相談チャット機能 LLM回答取得・表示方法 調査レポート

調査日時: 2025年12月4日

## 1. LLMからの回答取得処理

### 1.1 回答取得の流れ

**ファイル**: `app/Http/Controllers/Student/ConsultationController.php`

```php
// 98-99行目
$answer = $this->geminiService->generateConsultationAnswer($prompt);
```

**処理フロー**:
1. 質問文の正規化・重複チェック（52-65行目）
2. 全記事データ取得（70-71行目）
3. 修正履歴取得（74-84行目）
4. プロンプト作成（86-96行目）
5. Gemini API呼び出し（99行目）
6. データベースに保存（102-106行目）

### 1.2 プロンプトの構成方法

**プロンプト構造**（86-96行目）:

```
あなたはマーケティングコンサルタントのオズです。
以下の記事知識を参考に、クライアントの質問に答えてください。

【記事知識】
{全記事のcontentを結合（\n\nで区切る）}

【過去の間違い事例】（修正履歴がある場合のみ）
質問: {質問文}
間違った回答: {間違った回答}
正しい回答: {正しい回答}
（最大50件まで）

【クライアントの質問】
{質問文}

親身になって、具体的にアドバイスしてください。
```

### 1.3 ナレッジベースの活用

#### 記事データの取得方法
```php
// 70-71行目
$articles = Article::all();
$articlesContent = $articles->pluck('content')->implode("\n\n");
```
- **取得方法**: `Article::all()`で全記事を取得
- **結合方法**: `pluck('content')->implode("\n\n")`で記事本文を改行2つで結合
- **制限**: 記事数に制限なし（全記事をプロンプトに含める）

#### 修正履歴の活用方法
```php
// 74-84行目
$corrections = Correction::with('consultation')
    ->orderBy('created_at', 'desc')
    ->limit(50)
    ->get();

$correctionsText = '';
foreach ($corrections as $correction) {
    $correctionsText .= "質問: {$correction->consultation->question}\n";
    $correctionsText .= "間違った回答: {$correction->wrong_answer}\n";
    $correctionsText .= "正しい回答: {$correction->correct_answer}\n\n";
}
```
- **取得方法**: `Correction::with('consultation')`で関連するconsultationも取得
- **制限**: 最新50件まで
- **形式**: 質問、間違った回答、正しい回答の3行形式

### 1.4 回答取得の確認

**ファイル**: `app/Services/GeminiApiService.php`

```php
// 258-301行目: generateConsultationAnswer()メソッド
public function generateConsultationAnswer(string $prompt): string
{
    // Gemini API呼び出し
    $response = Http::timeout($this->timeout)
        ->post("{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);
    
    // レスポンスからテキストを抽出
    $body = $response->json();
    return trim($body['candidates'][0]['content']['parts'][0]['text']);
}
```

**確認結果**:
- ✅ **回答はLLM（Gemini API）からのみ取得**
- ✅ **固定テキストやテンプレートは使用していない**
- ✅ **リトライロジックあり**（最大3回、指数バックオフ）
- ✅ **タイムアウト設定あり**（デフォルト60秒）

## 2. 回答の保存方法

**ファイル**: `app/Http/Controllers/Student/ConsultationController.php`

```php
// 102-106行目
$consultation = Consultation::create([
    'question' => $question,
    'answer' => $answer,  // Gemini APIからの回答をそのまま保存
    'user_id' => null,
]);
```

**確認結果**:
- ✅ **Gemini APIからの回答をそのまま保存**
- ✅ **保存前に`trim()`のみ実行**（GeminiApiService内で実行）
- ✅ **その他の加工はなし**

## 3. 回答の表示方法

**ファイル**: `resources/js/Pages/Student/Consultations/Index.tsx`

### 3.1 現在の表示実装

```tsx
// 174-185行目
<div className="flex justify-start">
    <div className="max-w-2xl bg-gray-100 rounded-lg p-4">
        <p className="text-sm text-gray-700 whitespace-pre-wrap">
            {consultation.answer}
        </p>
        {consultation.is_corrected && (
            <p className="text-xs text-orange-600 mt-2">
                ※この回答は修正されています
            </p>
        )}
    </div>
</div>
```

### 3.2 表示方法の確認

**現在の実装**:
- ✅ **改行は正しく反映される**（`whitespace-pre-wrap`を使用）
- ❌ **Markdown記号はそのまま表示される**（`###`、`*`、`**`など）
- ❌ **Markdownレンダリングライブラリは使用していない**

**問題点**:
1. **見出し記号（`###`）がそのまま表示される**
   - 例: `### タイトル` → `### タイトル`（見出しとして表示されない）

2. **強調記号（`*`、`**`）がそのまま表示される**
   - 例: `**太字**` → `**太字**`（太字として表示されない）
   - 例: `*斜体*` → `*斜体*`（斜体として表示されない）

3. **リスト記号（`-`、`*`）がそのまま表示される**
   - 例: `- 項目1` → `- 項目1`（リストとして表示されない）

4. **リンク記号（`[text](url)`）がそのまま表示される**
   - 例: `[リンク](https://example.com)` → `[リンク](https://example.com)`（リンクとして表示されない）

## 4. 改善提案

### 4.1 Markdownレンダリングライブラリの導入

#### 推奨ライブラリ: `react-markdown`

**インストール**:
```bash
npm install react-markdown
```

**実装例**:
```tsx
import ReactMarkdown from 'react-markdown';

// 回答表示部分を変更
<div className="flex justify-start">
    <div className="max-w-2xl bg-gray-100 rounded-lg p-4">
        <ReactMarkdown className="text-sm text-gray-700 prose prose-sm max-w-none">
            {consultation.answer}
        </ReactMarkdown>
        {consultation.is_corrected && (
            <p className="text-xs text-orange-600 mt-2">
                ※この回答は修正されています
            </p>
        )}
    </div>
</div>
```

**追加のスタイリング（Tailwind CSS）**:
```bash
npm install @tailwindcss/typography
```

`tailwind.config.js`に追加:
```js
plugins: [
    require('@tailwindcss/typography'),
],
```

### 4.2 代替案: シンプルなMarkdownパーサー

**軽量な実装**（react-markdownを使わない場合）:

```tsx
// カスタムMarkdownコンポーネント
function SimpleMarkdown({ text }: { text: string }) {
    // 見出し
    let html = text.replace(/^### (.*$)/gim, '<h3 class="text-lg font-bold mt-4 mb-2">$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2 class="text-xl font-bold mt-4 mb-2">$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1 class="text-2xl font-bold mt-4 mb-2">$1</h1>');
    
    // 太字
    html = html.replace(/\*\*(.*?)\*\*/gim, '<strong class="font-bold">$1</strong>');
    
    // リスト
    html = html.replace(/^- (.*$)/gim, '<li class="ml-4">$1</li>');
    html = html.replace(/(<li.*<\/li>)/s, '<ul class="list-disc list-inside space-y-1">$1</ul>');
    
    // 改行
    html = html.replace(/\n/g, '<br />');
    
    return <div className="text-sm text-gray-700" dangerouslySetInnerHTML={{ __html: html }} />;
}
```

**注意**: `dangerouslySetInnerHTML`を使用する場合は、XSS対策が必要です。

### 4.3 CSSでの表示改善（現状維持の場合）

Markdownレンダリングを導入しない場合でも、最低限の改善:

```css
/* 見出し風のスタイル */
.consultation-answer h3 {
    font-size: 1.125rem;
    font-weight: bold;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

/* リスト風のスタイル */
.consultation-answer ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}
```

## 5. 現在の実装状況まとめ

### ✅ 実装済み
- LLM（Gemini API）からの回答取得
- ナレッジベース（articlesテーブル）の活用
- 修正履歴（correctionsテーブル）の活用
- 回答のデータベース保存
- 改行の正しい表示（`whitespace-pre-wrap`）

### ❌ 未実装・改善が必要
- Markdown記号のレンダリング
- 見出し、太字、リスト、リンクなどの装飾表示
- Markdownレンダリングライブラリの導入

## 6. 推奨される改善手順

### ステップ1: react-markdownの導入
```bash
npm install react-markdown
npm install @tailwindcss/typography
```

### ステップ2: ビューファイルの修正
`resources/js/Pages/Student/Consultations/Index.tsx`の回答表示部分を修正

### ステップ3: スタイリングの調整
Tailwind CSSの`prose`クラスを使用して、読みやすいスタイルを適用

### ステップ4: テスト
- 見出し（`###`）が正しく表示されるか
- 太字（`**`）が正しく表示されるか
- リスト（`-`）が正しく表示されるか
- リンク（`[text](url)`）が正しく表示されるか
- 改行が正しく保持されるか

## 7. 注意事項

### プロンプトの最適化
現在、全記事をプロンプトに含めているため、記事数が増えると以下の問題が発生する可能性があります：
- **トークン数の増加**: Gemini APIのトークン制限に達する可能性
- **コストの増加**: トークン数に応じてコストが増加
- **レスポンス時間の増加**: プロンプトが長いと処理時間が増加

**改善案**:
- 記事を関連性の高いものに絞り込む（ベクトル検索など）
- 記事の要約を作成してプロンプトに含める
- 記事数を制限する（例: 最新50件）

### 修正履歴の最適化
現在、最新50件の修正履歴を取得していますが、関連性の高い修正履歴のみを選択することで、より効果的な学習が可能です。

