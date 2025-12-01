<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Correction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DiscordService
{
    /**
     * 間違い修正通知をDiscordに送信
     */
    public function sendCorrection(Consultation $consultation, Correction $correction): bool
    {
        $webhookUrl = config('services.discord.correction_webhook');
        
        if (empty($webhookUrl)) {
            Log::info('Discord修正通知: Webhook URLが設定されていません');
            return false;
        }

        try {
            $message = [
                'content' => "【間違い修正】\n質問: {$consultation->question}\n間違い: {$correction->wrong_answer}\n正解: {$correction->correct_answer}" . 
                            ($correction->corrected_by ? "\n修正者: {$correction->corrected_by}" : '')
            ];

            $response = Http::timeout(10)->post($webhookUrl, $message);

            if (!$response->successful()) {
                Log::warning("Discord修正通知送信失敗: {$response->status()} - {$response->body()}");
                return false;
            }

            Log::info('Discord修正通知送信成功');
            return true;

        } catch (Exception $e) {
            Log::error("Discord修正通知送信エラー: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * 質問・回答履歴をDiscordに投稿
     */
    public function sendQA(Consultation $consultation): bool
    {
        $webhookUrl = config('services.discord.qa_webhook');
        
        if (empty($webhookUrl)) {
            Log::info('Discord QA投稿: Webhook URLが設定されていません');
            return false;
        }

        try {
            // 回答を長すぎる場合は切り詰め
            $answer = mb_strlen($consultation->answer) > 1000 
                ? mb_substr($consultation->answer, 0, 1000) . '...'
                : $consultation->answer;

            $message = [
                'content' => "【新規相談】\n質問: {$consultation->question}\n回答: {$answer}"
            ];

            $response = Http::timeout(10)->post($webhookUrl, $message);

            if (!$response->successful()) {
                Log::warning("Discord QA投稿送信失敗: {$response->status()} - {$response->body()}");
                return false;
            }

            Log::info('Discord QA投稿送信成功');
            return true;

        } catch (Exception $e) {
            Log::error("Discord QA投稿送信エラー: {$e->getMessage()}");
            return false;
        }
    }
}

