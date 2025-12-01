<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Article;
use App\Services\GeminiApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    public function __construct(
        private GeminiApiService $geminiService
    ) {}

    /**
     * 質問一覧を表示
     */
    public function index(): Response
    {
        $questions = Question::orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Questions/Index', [
            'questions' => $questions,
        ])->with('errors', session('errors'));
    }

    /**
     * 質問登録フォームを表示
     */
    public function create(): Response
    {
        return Inertia::render('Questions/Create');
    }

    /**
     * 質問を保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        Question::create([
            'question' => $validated['question'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('questions.index')
            ->with('success', '質問を登録しました');
    }

    /**
     * 質問編集フォームを表示
     */
    public function edit(Question $question): Response
    {
        return Inertia::render('Questions/Edit', [
            'question' => $question,
        ]);
    }

    /**
     * 質問を更新
     */
    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        $question->update($validated);

        return redirect()->route('questions.index')
            ->with('success', '質問を更新しました');
    }

    /**
     * 質問を削除
     */
    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('questions.index')
            ->with('success', '質問を削除しました');
    }

    /**
     * 質問を生成（Gemini API）
     */
    public function generate()
    {
        try {
            // 全記事を取得
            $articles = Article::all();

            if ($articles->isEmpty()) {
                return redirect()->route('questions.index')
                    ->with('error', '記事が登録されていません。先に記事を登録してください。');
            }

            // プロンプト生成
            $prompt = $this->buildQuestionPrompt($articles);

            // Gemini API呼び出し
            $result = $this->geminiService->generateQuestions($prompt);

            // questionsテーブルに保存
            foreach ($result['questions'] as $index => $questionText) {
                Question::create([
                    'question' => $questionText,
                    'sort_order' => $index + 1,
                ]);
            }

            return redirect()->route('questions.index')
                ->with('success', '質問を生成しました');

        } catch (\Exception $e) {
            Log::error('質問生成エラー: ' . $e->getMessage());

            return redirect()->route('questions.index')
                ->with('error', '質問の生成に失敗しました。しばらく時間をおいて再度お試しください。');
        }
    }

    /**
     * 質問を再生成
     */
    public function regenerate()
    {
        try {
            // 既存の質問をすべて削除
            Question::truncate();

            // 質問生成処理を実行
            return $this->generate();

        } catch (\Exception $e) {
            Log::error('質問再生成エラー: ' . $e->getMessage());

            return redirect()->route('questions.index')
                ->with('error', '質問の再生成に失敗しました。');
        }
    }

    /**
     * 質問生成プロンプトを構築
     */
    private function buildQuestionPrompt($articles): string
    {
        // 記事本文を結合
        $articlesContent = $articles->pluck('content')->implode("\n\n");

        $articleCount = $articles->count();
        
        $prompt = "以下の記事データを分析して、クライアントに対するヒアリング質問リストを作成してください。\n\n";
        $prompt .= "【記事データ】\n{$articlesContent}\n\n";
        $prompt .= "【要件】\n";
        $prompt .= "- ビジネス向けプロフィールと商品設計を作成するための質問\n";
        
        if ($articleCount < 3) {
            $prompt .= "- 記事が少ないため、基本的な質問を中心に生成してください\n";
            $prompt .= "- 10〜12個程度の質問を生成\n";
        } else {
            $prompt .= "- 10〜15個程度の質問を生成\n";
        }
        
        $prompt .= "- 具体的で答えやすい質問にする\n";
        $prompt .= "- **以下の基本質問を必ず含めてください**:\n";
        $prompt .= "  1. ターゲット顧客（年齢、性別、職業、悩み）\n";
        $prompt .= "  2. 提供するサービス/商品の内容\n";
        $prompt .= "  3. 競合との差別化ポイント・強み\n";
        $prompt .= "  4. 価格帯\n";
        $prompt .= "  5. 提供方法（オンライン/オフライン等）\n";
        $prompt .= "  6. 顧客に提供したい未来\n";
        
        if ($articleCount >= 3) {
            $prompt .= "- 上記以外にも、記事データに基づいた追加の質問を生成してください\n";
        } else {
            $prompt .= "- 記事データから読み取れる情報を参考に、関連する質問を追加してください\n";
        }
        $prompt .= "\n";
        $prompt .= "【出力形式】\n";
        $prompt .= "以下のJSON形式で必ず出力してください：\n\n";
        $prompt .= "{\n";
        $prompt .= '  "questions": [' . "\n";
        $prompt .= '    "あなたのターゲット顧客はどのような人ですか？（年齢、職業、悩みなど）",' . "\n";
        $prompt .= '    "現在提供している（または提供したい）サービスや商品は何ですか？",' . "\n";
        $prompt .= "    ...\n";
        $prompt .= "  ]\n";
        $prompt .= "}\n\n";
        $prompt .= "【注意事項】\n";
        $prompt .= "- JSON形式のみを返却し、説明文は不要\n";
        $prompt .= "- questionsは配列形式\n";

        return $prompt;
    }
}
