<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiApiService
{
    private string $apiKey;
    private string $model;
    private int $timeout;
    private int $maxRetries;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-pro');
        $this->timeout = config('services.gemini.timeout', 60);
        $this->maxRetries = config('services.gemini.max_retries', 3);
    }

    /**
     * Gemini APIを呼び出して提案を生成
     * 
     * @param string $prompt
     * @return array
     * @throws Exception
     */
    public function generateProposal(string $prompt): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
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

                if (!$response->successful()) {
                    throw new Exception("API呼び出し失敗: {$response->status()} - {$response->body()}");
                }

                $body = $response->json();
                
                if (!isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                    throw new Exception('APIレスポンスの形式が不正です');
                }

                $text = $body['candidates'][0]['content']['parts'][0]['text'];
                
                // JSONをパース
                $decoded = json_decode($text, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('JSONのパースに失敗しました: ' . json_last_error_msg());
                }

                // 必須フィールドの検証
                $requiredFields = ['x_profile', 'instagram_profile', 'coconala_profile', 'product_design'];
                foreach ($requiredFields as $field) {
                    if (!isset($decoded[$field])) {
                        throw new Exception("必須フィールドが不足しています: {$field}");
                    }
                }

                return $decoded;
            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $this->maxRetries) {
                    $waitTime = pow(2, $attempt - 1); // 指数バックオフ
                    Log::warning("Gemini API呼び出し失敗（試行 {$attempt}/{$this->maxRetries}）: {$e->getMessage()}");
                    sleep($waitTime);
                }
            }
        }

        throw new Exception("提案の生成に失敗しました: {$lastException->getMessage()}");
    }
    
    /**
     * 質問リストを生成
     * 
     * @param string $prompt
     * @return array
     * @throws Exception
     */
    public function generateQuestions(string $prompt): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
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

                if (!$response->successful()) {
                    throw new Exception("API呼び出し失敗: {$response->status()} - {$response->body()}");
                }

                $body = $response->json();
                
                if (!isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                    throw new Exception('APIレスポンスの形式が不正です');
                }

                $text = $body['candidates'][0]['content']['parts'][0]['text'];
                
                // JSONをパース
                $decoded = json_decode($text, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('JSONのパースに失敗しました: ' . json_last_error_msg());
                }

                // 必須フィールドの検証
                if (!isset($decoded['questions']) || !is_array($decoded['questions'])) {
                    throw new Exception('questionsフィールドが不正です');
                }

                return $decoded;
            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $this->maxRetries) {
                    $waitTime = pow(2, $attempt - 1);
                    Log::warning("Gemini API呼び出し失敗（試行 {$attempt}/{$this->maxRetries}）: {$e->getMessage()}");
                    sleep($waitTime);
                }
            }
        }

        throw new Exception("質問の生成に失敗しました: {$lastException->getMessage()}");
    }
}

