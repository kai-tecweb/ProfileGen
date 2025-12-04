<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Article;
use App\Models\Correction;
use App\Services\GeminiApiService;
use App\Services\DiscordService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class ConsultationController extends Controller
{
    public function __construct(
        private GeminiApiService $geminiService,
        private DiscordService $discordService
    ) {}

    /**
     * 相談チャット画面を表示
     */
    public function index(): Response
    {
        $consultations = Consultation::orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // セッションから新しい相談を取得（重複質問の場合）
        $newConsultation = session('new_consultation');

        return Inertia::render('Student/Consultations/Index', [
            'consultations' => $consultations,
            'new_consultation' => $newConsultation,
        ]);
    }

    /**
     * 相談を保存・回答生成
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:2000',
        ]);

        $question = trim($validated['question']);
        
        // 質問文の正規化（小文字、空白除去）して完全一致チェック
        $normalizedQuestion = $this->normalizeQuestion($question);
        
        // 既存の同じ質問を検索（正規化した質問文で完全一致チェック）
        $existingConsultation = Consultation::whereRaw('LOWER(TRIM(question)) = ?', [strtolower($normalizedQuestion)])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existingConsultation) {
            // 重複質問でも新しいレコードを保存（履歴に表示するため）
            \Log::info('重複質問検出', [
                'question' => $question,
                'existing_id' => $existingConsultation->id,
                'existing_answer' => $existingConsultation->answer ? 'あり' : 'なし',
                'existing_answer_length' => $existingConsultation->answer ? strlen($existingConsultation->answer) : 0,
            ]);
            
            $newConsultation = Consultation::create([
                'question' => $question,
                'answer' => $existingConsultation->answer,  // 既存の回答を使用
                'user_id' => null, // 学生側はuser_idなし
                'is_corrected' => $existingConsultation->is_corrected,
            ]);
            
            \Log::info('新しいレコード作成', [
                'id' => $newConsultation->id,
                'question' => $newConsultation->question,
                'answer' => $newConsultation->answer ? 'あり' : 'なし',
                'answer_length' => $newConsultation->answer ? strlen($newConsultation->answer) : 0,
            ]);

            // セッションに新しい相談を追加（リダイレクト直後に確実に表示されるように）
            return redirect()->route('student.consultations.index')
                ->with('warning', 'この質問は過去に同じ質問があります。既存の回答を表示しています。')
                ->with('new_consultation', $newConsultation);
        }

        // 新規質問の場合
        try {
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

            // プロンプト作成
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
            $prompt .= "- 記事知識に関連する情報がない場合は、以下のように簡潔に回答してください：\n\n";
            $prompt .= "【要約】\n";
            $prompt .= "申し訳ございません。ご質問の内容は記事知識（ナレッジベース）に含まれていないため、お答えすることができません。\n\n";
            $prompt .= "【今すぐやること】\n";
            $prompt .= "• この相談チャットは、用意された記事知識に基づいて回答するシステムです\n";
            $prompt .= "• 別の質問がございましたら、お気軽にお尋ねください\n\n";
            $prompt .= "【アドバイス】\n";
            $prompt .= "• ココナラでの活動、GPTsの活用、マーケティング戦略など、記事知識に含まれる内容についてご質問ください\n\n";
            $prompt .= "【詳細】\n";
            $prompt .= "このシステムは、事前に用意された記事知識（ナレッジベース）を参照して回答を生成します。ご質問の内容が記事知識に含まれていない場合、正確な情報を提供することができません。\n\n";
            $prompt .= "- 一般的な知識で無理に回答しないでください\n";
            $prompt .= "- ナレッジに無い内容は明確に断ってください\n";
            $prompt .= "- 相談の範囲外であることを伝えてください\n\n";
            $prompt .= "【ナレッジにある質問の場合の厳守事項】\n";
            $prompt .= "- 記事知識に書かれている内容**のみ**を使用してください\n";
            $prompt .= "- AIとしての独自の推測・憶測は一切加えないでください\n";
            $prompt .= "- 記事に書かれていない情報は追加しないでください\n";
            $prompt .= "- 記事の内容を忠実に、分かりやすく整理して伝えてください\n";
            $prompt .= "- 「もしかしたら」「おそらく」「推測すると」「一般的には」などの表現は使わないでください\n";
            $prompt .= "- 記事に書かれている事実・手順・アドバイスをそのまま伝えてください\n";
            $prompt .= "- 記事に無い事例、記事に無い応用、記事に無い背景説明は絶対に追加しないでください\n\n";
            $prompt .= "【回答の作り方】\n";
            $prompt .= "- 【要約】：記事の要点をまとめる（記事の内容のみ）\n";
            $prompt .= "- 【今すぐやること】：記事に書かれている具体的なアクション（記事の内容のみ）\n";
            $prompt .= "- 【アドバイス】：記事に書かれている心構えや注意点（記事の内容のみ）\n";
            $prompt .= "- 【詳細】：記事の詳しい内容を整理して記載（記事の内容のみ）\n\n";
            $prompt .= "記事に書かれていないことは絶対に追加しないでください。\n\n";
            $prompt .= "【クライアントの質問】\n{$question}\n\n";
            $prompt .= "上記のフォーマットに従って、親身になって具体的にアドバイスしてください。";

            // Gemini APIで回答生成
            $answer = $this->geminiService->generateConsultationAnswer($prompt);

            // DBに保存
            $consultation = Consultation::create([
                'question' => $question,
                'answer' => $answer,
                'user_id' => null, // 学生側はuser_idなし
            ]);

            // Discord投稿（設定でONの場合）
            if (config('services.discord.notify_all_qa')) {
                $this->discordService->sendQA($consultation);
            }

            return redirect()->route('student.consultations.index')
                ->with('success', '回答を生成しました')
                ->with('new_consultation', $consultation);

        } catch (\Exception $e) {
            Log::error('相談回答生成エラー: ' . $e->getMessage());

            return redirect()->route('student.consultations.index')
                ->with('error', '回答の生成に失敗しました。しばらく時間をおいて再度お試しください。');
        }
    }

    /**
     * 質問文を正規化（比較用）
     */
    private function normalizeQuestion(string $question): string
    {
        // 空白を除去、小文字に変換
        return strtolower(trim($question));
    }
}

