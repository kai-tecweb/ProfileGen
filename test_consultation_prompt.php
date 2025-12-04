<?php
/**
 * 相談チャット機能のプロンプトテストスクリプト
 * 
 * 使用方法:
 * php test_consultation_prompt.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Models\Correction;
use App\Services\GeminiApiService;

// Gemini APIサービスのインスタンスを作成
$geminiService = app(GeminiApiService::class);

// テスト質問1: ナレッジにある内容
$question1 = "ココナラで商品を初めて出品する時の手順を教えてください";

// テスト質問2: ナレッジに無い内容
$question2 = "明日の東京の天気予報を教えてください";

echo "=== 相談チャット機能 プロンプトテスト ===\n\n";

// 全記事の本文を取得
$articles = Article::all();
$articlesContent = $articles->pluck('content')->implode("\n\n");

// correctionsテーブルから過去の間違い事例を取得
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

// プロンプト作成（修正後の形式）
function createPrompt($question, $articlesContent, $correctionsText) {
    $prompt = "あなたはマーケティングコンサルタントのオズです。\n\n";
    $prompt .= "【絶対に守るべきルール】\n";
    $prompt .= "回答は必ず以下の4つのセクションに分けて記載してください。\n";
    $prompt .= "各セクションの見出し（【要約】【今すぐやること】【アドバイス】【詳細】）は必ず記載してください。\n\n";
    $prompt .= "【回答フォーマット】\n\n";
    $prompt .= "【要約】\n";
    $prompt .= "（ここに2-3行で要点を記載）\n\n";
    $prompt .= "【今すぐやること】\n";
    $prompt .= "• アクション1\n";
    $prompt .= "• アクション2\n";
    $prompt .= "• アクション3\n\n";
    $prompt .= "【アドバイス】\n";
    $prompt .= "• アドバイス1\n";
    $prompt .= "• アドバイス2\n\n";
    $prompt .= "【詳細】\n";
    $prompt .= "（ここに詳しい説明を記載）\n\n";
    $prompt .= "---\n\n";
    $prompt .= "【記事知識】\n{$articlesContent}\n\n";
    
    if (!empty($correctionsText)) {
        $prompt .= "【過去の間違い事例】\n{$correctionsText}\n\n";
    }
    
    $prompt .= "【重要】\n";
    $prompt .= "- もし記事知識に関連する情報がない場合は、【要約】セクションで「記事知識には該当する情報がありませんでした」と明記した上で、一般的な知識で回答してください。\n";
    $prompt .= "- ナレッジに無い内容でも、できる限り役立つアドバイスを提供してください。\n\n";
    $prompt .= "【クライアントの質問】\n{$question}\n\n";
    $prompt .= "上記のフォーマットに従って、親身になって具体的にアドバイスしてください。";
    
    return $prompt;
}

// テスト1: ナレッジにある質問
echo "【テスト1】ナレッジにある質問\n";
echo "質問: {$question1}\n";
echo "---\n";
$prompt1 = createPrompt($question1, $articlesContent, $correctionsText);
echo "プロンプト（最初の500文字）:\n";
echo substr($prompt1, 0, 500) . "...\n\n";

try {
    echo "Gemini APIを呼び出しています...\n";
    $answer1 = $geminiService->generateConsultationAnswer($prompt1);
    echo "回答:\n";
    echo $answer1 . "\n\n";
    
    // 4段階構造のチェック
    $hasSummary = strpos($answer1, '【要約】') !== false;
    $hasAction = strpos($answer1, '【今すぐやること】') !== false;
    $hasAdvice = strpos($answer1, '【アドバイス】') !== false;
    $hasDetail = strpos($answer1, '【詳細】') !== false;
    
    echo "=== 4段階構造チェック ===\n";
    echo "【要約】: " . ($hasSummary ? "✓" : "✗") . "\n";
    echo "【今すぐやること】: " . ($hasAction ? "✓" : "✗") . "\n";
    echo "【アドバイス】: " . ($hasAdvice ? "✓" : "✗") . "\n";
    echo "【詳細】: " . ($hasDetail ? "✓" : "✗") . "\n";
    
    if ($hasSummary && $hasAction && $hasAdvice && $hasDetail) {
        echo "\n✓ 4段階構造が正しく実装されています！\n";
    } else {
        echo "\n✗ 4段階構造が不完全です。プロンプトを再確認してください。\n";
    }
} catch (\Exception $e) {
    echo "エラー: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// テスト2: ナレッジに無い質問
echo "【テスト2】ナレッジに無い質問\n";
echo "質問: {$question2}\n";
echo "---\n";
$prompt2 = createPrompt($question2, $articlesContent, $correctionsText);
echo "プロンプト（最初の500文字）:\n";
echo substr($prompt2, 0, 500) . "...\n\n";

try {
    echo "Gemini APIを呼び出しています...\n";
    $answer2 = $geminiService->generateConsultationAnswer($prompt2);
    echo "回答:\n";
    echo $answer2 . "\n\n";
    
    // 4段階構造のチェック
    $hasSummary = strpos($answer2, '【要約】') !== false;
    $hasAction = strpos($answer2, '【今すぐやること】') !== false;
    $hasAdvice = strpos($answer2, '【アドバイス】') !== false;
    $hasDetail = strpos($answer2, '【詳細】') !== false;
    $hasNoKnowledge = strpos($answer2, '記事知識には該当する情報がありませんでした') !== false;
    
    echo "=== 4段階構造チェック ===\n";
    echo "【要約】: " . ($hasSummary ? "✓" : "✗") . "\n";
    echo "【今すぐやること】: " . ($hasAction ? "✓" : "✗") . "\n";
    echo "【アドバイス】: " . ($hasAdvice ? "✓" : "✗") . "\n";
    echo "【詳細】: " . ($hasDetail ? "✓" : "✗") . "\n";
    echo "ナレッジ無しの明記: " . ($hasNoKnowledge ? "✓" : "✗") . "\n";
    
    if ($hasSummary && $hasAction && $hasAdvice && $hasDetail) {
        echo "\n✓ 4段階構造が正しく実装されています！\n";
        if ($hasNoKnowledge) {
            echo "✓ ナレッジに無い質問への対応も正しく実装されています！\n";
        } else {
            echo "⚠ ナレッジに無い質問への対応が明記されていません。\n";
        }
    } else {
        echo "\n✗ 4段階構造が不完全です。プロンプトを再確認してください。\n";
    }
} catch (\Exception $e) {
    echo "エラー: " . $e->getMessage() . "\n";
}

echo "\n=== テスト完了 ===\n";

