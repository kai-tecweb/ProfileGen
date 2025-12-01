<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ImageService
{
    private int $timeout = 30;
    private int $maxSize = 20 * 1024 * 1024; // 20MB

    /**
     * 画像URLをダウンロードしてBase64エンコード
     * 
     * @param string $imageUrl
     * @return array|null ['mime_type' => string, 'data' => string]
     * @throws Exception
     */
    public function downloadAndEncode(string $imageUrl): ?array
    {
        try {
            // 有効な画像URLかチェック
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                Log::warning("無効な画像URL: {$imageUrl}");
                return null;
            }

            // 画像をダウンロード
            $response = Http::timeout($this->timeout)
                ->withOptions([
                    'verify' => false, // SSL証明書の検証をスキップ（必要に応じて調整）
                    'allow_redirects' => true,
                ])
                ->get($imageUrl);

            if (!$response->successful()) {
                Log::warning("画像ダウンロード失敗: {$imageUrl} - ステータス: {$response->status()}");
                return null;
            }

            $imageData = $response->body();
            $contentLength = strlen($imageData);

            // サイズチェック
            if ($contentLength > $this->maxSize) {
                Log::warning("画像サイズが大きすぎます: {$imageUrl} - {$contentLength} bytes");
                return null;
            }

            // MIMEタイプを取得
            $mimeType = $response->header('Content-Type');
            if (!$mimeType) {
                // Content-Typeヘッダーがない場合、URLから推測
                $mimeType = $this->guessMimeTypeFromUrl($imageUrl);
            }

            // 画像形式をチェック
            if (!$this->isValidImageMimeType($mimeType)) {
                Log::warning("サポートされていない画像形式: {$imageUrl} - MIME: {$mimeType}");
                return null;
            }

            // Base64エンコード
            $base64Data = base64_encode($imageData);

            return [
                'mime_type' => $mimeType,
                'data' => $base64Data,
            ];

        } catch (Exception $e) {
            Log::warning("画像ダウンロードエラー: {$imageUrl} - {$e->getMessage()}");
            return null;
        }
    }

    /**
     * 複数の画像URLをダウンロードしてBase64エンコード
     * 
     * @param array $imageUrls
     * @return array
     */
    public function downloadMultiple(array $imageUrls): array
    {
        $results = [];
        
        foreach ($imageUrls as $url) {
            $result = $this->downloadAndEncode($url);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * URLからMIMEタイプを推測
     */
    private function guessMimeTypeFromUrl(string $url): string
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];

        return $mimeTypes[$extension] ?? 'image/jpeg';
    }

    /**
     * 有効な画像MIMEタイプかチェック
     */
    private function isValidImageMimeType(string $mimeType): bool
    {
        $validTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ];

        return in_array(strtolower($mimeType), $validTypes);
    }
}

