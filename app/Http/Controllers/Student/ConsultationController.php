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

        // セッションから既存の相談を取得（既存質問の場合）
        $existingConsultation = session('existing_consultation');

        return Inertia::render('Student/Consultations/Index', [
            'consultations' => $consultations,
            'existing_consultation' => $existingConsultation,
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
            // 既存回答がある場合
            return redirect()->route('student.consultations.index')
                ->with('warning', '同じ質問なので同じ回答をします')
                ->with('existing_consultation', $existingConsultation);
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

