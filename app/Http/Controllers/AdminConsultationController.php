<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Correction;
use App\Services\DiscordService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class AdminConsultationController extends Controller
{
    public function __construct(
        private DiscordService $discordService
    ) {}

    /**
     * 相談履歴一覧を表示
     */
    public function index(): Response
    {
        $consultations = Consultation::with('corrections')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Consultations/Index', [
            'consultations' => $consultations,
        ]);
    }

    /**
     * 回答を修正
     */
    public function correct(Request $request, Consultation $consultation)
    {
        $validated = $request->validate([
            'correct_answer' => 'required|string',
            'corrected_by' => 'nullable|string|max:255',
        ]);

        try {
            // correctionsテーブルに保存
            $correction = Correction::create([
                'consultation_id' => $consultation->id,
                'wrong_answer' => $consultation->answer,
                'correct_answer' => $validated['correct_answer'],
                'corrected_by' => $validated['corrected_by'] ?? auth()->user()->name ?? null,
            ]);

            // consultationsテーブルのis_correctedをtrueに
            $consultation->update([
                'is_corrected' => true,
            ]);

            // Discord投稿（間違い修正通知）
            $this->discordService->sendCorrection($consultation, $correction);

            return redirect()->route('admin.consultations.index')
                ->with('success', '回答を修正しました。Discordに通知を送信しました。');

        } catch (\Exception $e) {
            Log::error('回答修正エラー: ' . $e->getMessage());

            return back()->withErrors([
                'error' => '回答の修正に失敗しました。',
            ]);
        }
    }

    /**
     * 質問・回答をDiscordに投稿（手動実行用）
     */
    public function notifyDiscord(Consultation $consultation)
    {
        try {
            $this->discordService->sendQA($consultation);

            return redirect()->route('admin.consultations.index')
                ->with('success', 'Discordに投稿しました。');

        } catch (\Exception $e) {
            Log::error('Discord投稿エラー: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Discord投稿に失敗しました。',
            ]);
        }
    }
}
