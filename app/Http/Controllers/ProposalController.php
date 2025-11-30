<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Article;
use App\Services\GeminiApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class ProposalController extends Controller
{
    public function __construct(
        private GeminiApiService $geminiService
    ) {}

    /**
     * 提案詳細を表示
     */
    public function show(Proposal $proposal): Response
    {
        $proposal->load('client');

        return Inertia::render('Proposals/Show', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * 提案編集フォームを表示
     */
    public function edit(Proposal $proposal): Response
    {
        return Inertia::render('Proposals/Edit', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * 提案を更新
     */
    public function update(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'x_profile' => 'required|string',
            'instagram_profile' => 'required|string',
            'coconala_profile' => 'required|string',
            'product_design' => 'required|string',
        ]);

        $proposal->update($validated);

        return redirect()->route('proposals.show', $proposal)
            ->with('success', '提案を更新しました');
    }

    /**
     * 提案を削除
     */
    public function destroy(Proposal $proposal)
    {
        $clientId = $proposal->client_id;
        $proposal->delete();

        return redirect()->route('clients.show', $clientId)
            ->with('success', '提案を削除しました');
    }

    /**
     * 提案を生成
     */
    public function generate(Client $client)
    {
        try {
            // ヒアリング回答のチェック
            if (empty($client->answers_text)) {
                return back()->withErrors([
                    'error' => 'ヒアリング回答が未入力です。先にクライアント情報を編集してヒアリング回答を入力してください。'
                ]);
            }

            // 全記事を取得
            $articles = Article::all();

            if ($articles->isEmpty()) {
                return back()->withErrors([
                    'error' => '記事が登録されていません。先に記事を登録してください。'
                ]);
            }

            // プロンプト生成
            $prompt = $this->buildPrompt($client, $articles);

            // Gemini API呼び出し
            $result = $this->geminiService->generateProposal($prompt);

            // 提案を保存
            $proposal = Proposal::create([
                'client_id' => $client->id,
                'x_profile' => $result['x_profile'],
                'instagram_profile' => $result['instagram_profile'],
                'coconala_profile' => $result['coconala_profile'],
                'product_design' => $result['product_design'],
            ]);

            return redirect()->route('proposals.show', $proposal)
                ->with('success', '提案を生成しました');

        } catch (\Exception $e) {
            Log::error('提案生成エラー: ' . $e->getMessage());

            return back()->withErrors([
                'error' => '提案の生成に失敗しました。しばらく時間をおいて再度お試しください。'
            ]);
        }
    }

    /**
     * プロンプトを構築
     */
    private function buildPrompt(Client $client, $articles): string
    {
        // 記事本文を結合
        $articlesContent = $articles->pluck('content')->implode("\n\n");

        $prompt = "以下の記事データとクライアントのヒアリング回答を分析して、「{$client->name}」様向けのプロフィールと商品設計を提案してください。\n\n";
        $prompt .= "【記事データ】\n{$articlesContent}\n\n";
        $prompt .= "【クライアント情報】\n";
        $prompt .= "名前: {$client->name}\n";
        $prompt .= "会社: " . ($client->company ?? '') . "\n";
        $prompt .= "クライアント特徴: " . ($client->memo ?? '') . "\n\n";
        $prompt .= "【ヒアリング回答】\n{$client->answers_text}\n\n";
        $prompt .= "【指示】\n";
        $prompt .= "- 記事データから学んだマーケティングノウハウを活用してください\n";
        $prompt .= "- クライアント特徴を踏まえて、パーソナライズされた提案をしてください\n";
        $prompt .= "- ヒアリング回答を最大限活用してください\n";
        $prompt .= "- 回答が不完全な場合は、記事データや一般的なビジネス知識から推測して補完してください\n";
        $prompt .= "- クライアントの強みやターゲットを明確にしてください\n";
        $prompt .= "- クライアントの人柄に合った表現やトーンを使用してください\n\n";
        $prompt .= "【出力形式】\n";
        $prompt .= "以下のJSON形式で必ず出力してください。JSON以外のテキストは含めないでください：\n\n";
        $prompt .= "{\n";
        $prompt .= '  "x_profile": "X用プロフィール（160文字以内で簡潔に）",' . "\n";
        $prompt .= '  "instagram_profile": "Instagram用プロフィール（150文字以内で簡潔に）",' . "\n";
        $prompt .= '  "coconala_profile": "ココナラ用プロフィール（詳しく、サービス内容や特徴を含む）",' . "\n";
        $prompt .= '  "product_design": "商品設計案（価格、内容、ターゲット、提供方法等を含む詳細な提案）"' . "\n";
        $prompt .= "}\n\n";
        $prompt .= "【注意事項】\n";
        $prompt .= "- x_profileは160文字以内で簡潔に\n";
        $prompt .= "- instagram_profileは150文字以内で簡潔に\n";
        $prompt .= "- coconala_profileとproduct_designは詳細に記述\n";
        $prompt .= "- JSON形式のみを返却し、説明文は不要\n";

        return $prompt;
    }
}
