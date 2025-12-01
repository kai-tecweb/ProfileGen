<?php

namespace App\Http\Controllers;

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

        return Inertia::render('Consultations/Index', [
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
            return redirect()->route('consultations.index')
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
                ->limit(10)
                ->get();

            $correctionsText = '';
            foreach ($corrections as $correction) {
                $correctionsText .= "質問: {$correction->consultation->question}\n";
                $correctionsText .= "間違った回答: {$correction->wrong_answer}\n";
                $correctionsText .= "正しい回答: {$correction->correct_answer}\n\n";
            }

            // プロンプト作成
            $prompt = "あなたはマーケティングコンサルタントのオズです。\n";
            $prompt .= "以下の記事知識を参考に、クライアントの質問に答えてください。\n\n";
            $prompt .= "【記事知識】\n{$articlesContent}\n\n";
            
            if (!empty($correctionsText)) {
                $prompt .= "【過去の間違い事例】\n{$correctionsText}\n";
            }
            
            $prompt .= "【クライアントの質問】\n{$question}\n\n";
            $prompt .= "親身になって、具体的にアドバイスしてください。";

            // Gemini APIで回答生成
            $answer = $this->geminiService->generateConsultationAnswer($prompt);

            // DBに保存
            $consultation = Consultation::create([
                'question' => $question,
                'answer' => $answer,
                'user_id' => auth()->id(),
            ]);

            // Discord投稿（設定でONの場合）
            if (config('services.discord.notify_all_qa')) {
                $this->discordService->sendQA($consultation);
            }

            return redirect()->route('consultations.index')
                ->with('success', '回答を生成しました')
                ->with('new_consultation', $consultation);

        } catch (\Exception $e) {
            Log::error('相談回答生成エラー: ' . $e->getMessage());

            return redirect()->route('consultations.index')
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
