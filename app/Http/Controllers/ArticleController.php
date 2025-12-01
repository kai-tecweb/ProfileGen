<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ImageService;
use App\Services\GeminiApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    public function __construct(
        private ImageService $imageService,
        private GeminiApiService $geminiService
    ) {}
    /**
     * 記事一覧を表示
     */
    public function index(): Response
    {
        $articles = Article::orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
        ]);
    }

    /**
     * 記事登録フォームを表示
     */
    public function create(): Response
    {
        return Inertia::render('Articles/Create');
    }

    /**
     * 記事を保存
     */
    public function store(Request $request)
    {
        Log::info("記事保存処理開始: タイトル = " . $request->input('title', '未設定'));
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'auto_extract' => 'nullable|boolean',
        ]);

        $content = $validated['content'];
        $imageUrls = [];

        // まず、元のコンテンツ（HTMLまたはMarkdown）からURLを抽出
        Log::info("URL抽出処理開始（元コンテンツ）: コンテンツの長さ = " . strlen($content));
        $extractedUrl = $this->extractUrlFromContent($content);
        Log::info("記事保存: 抽出されたURL（元コンテンツ） = " . ($extractedUrl ?? 'null'));

        // HTMLから本文を自動抽出する場合
        if ($request->boolean('auto_extract')) {
            Log::info("HTML自動抽出が有効");
            
            // HTMLから直接URLを抽出（元コンテンツで見つからなかった場合）
            if (empty($extractedUrl)) {
                Log::info("HTMLから直接URL抽出を試行");
                $extractedUrl = $this->extractUrlFromHtml($content);
                if ($extractedUrl) {
                    Log::info("HTMLから直接URL抽出成功: {$extractedUrl}");
                }
            }
            
            $extracted = $this->extractContentAndImagesFromHtml($content);
            $content = $extracted['content'];
            $imageUrls = $extracted['image_urls'];
            
            // HTML抽出後のコンテンツからもURLを抽出してみる（元コンテンツで見つからなかった場合）
            if (empty($extractedUrl)) {
                Log::info("URL抽出処理開始（HTML抽出後）: コンテンツの長さ = " . strlen($content));
                $extractedUrl = $this->extractUrlFromContent($content);
                Log::info("記事保存: 抽出されたURL（HTML抽出後） = " . ($extractedUrl ?? 'null'));
            }
        }

        $article = Article::create([
            'title' => $validated['title'],
            'content' => $content,
            'url' => $extractedUrl,
            'image_urls' => !empty($imageUrls) ? $imageUrls : null,
        ]);
        
        Log::info("記事保存完了: ID = {$article->id}, URL = " . ($article->url ?? 'null'));

        // 続けて登録ボタンが押された場合
        if ($request->input('continue_registration')) {
            return redirect()->route('articles.create')
                ->with('success', '記事を登録しました。続けて登録できます。');
        }

        return redirect()->route('articles.index')
            ->with('success', '記事を登録しました');
    }

    /**
     * 記事編集フォームを表示
     */
    public function edit(Article $article): Response
    {
        return Inertia::render('Articles/Edit', [
            'article' => $article,
        ]);
    }

    /**
     * 記事を更新
     */
    public function update(Request $request, Article $article)
    {
        Log::info("記事更新処理開始: ID = {$article->id}, タイトル = " . $request->input('title', '未設定'));
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'auto_extract' => 'nullable|boolean',
        ]);

        $content = $validated['content'];
        $imageUrls = $article->image_urls ?? [];

        // まず、元のコンテンツ（HTMLまたはMarkdown）からURLを抽出
        Log::info("URL抽出処理開始（元コンテンツ）: コンテンツの長さ = " . strlen($content));
        $extractedUrl = $this->extractUrlFromContent($content);
        Log::info("記事更新: 抽出されたURL（元コンテンツ） = " . ($extractedUrl ?? 'null'));

        // HTMLから本文を自動抽出する場合
        if ($request->boolean('auto_extract')) {
            Log::info("HTML自動抽出が有効");
            
            // HTMLから直接URLを抽出（元コンテンツで見つからなかった場合）
            if (empty($extractedUrl)) {
                Log::info("HTMLから直接URL抽出を試行");
                $extractedUrl = $this->extractUrlFromHtml($content);
                if ($extractedUrl) {
                    Log::info("HTMLから直接URL抽出成功: {$extractedUrl}");
                }
            }
            
            $extracted = $this->extractContentAndImagesFromHtml($content);
            $content = $extracted['content'];
            $imageUrls = $extracted['image_urls'];
            
            // HTML抽出後のコンテンツからもURLを抽出してみる（元コンテンツで見つからなかった場合）
            if (empty($extractedUrl)) {
                Log::info("URL抽出処理開始（HTML抽出後）: コンテンツの長さ = " . strlen($content));
                $extractedUrl = $this->extractUrlFromContent($content);
                Log::info("記事更新: 抽出されたURL（HTML抽出後） = " . ($extractedUrl ?? 'null'));
            }
        }

        $article->update([
            'title' => $validated['title'],
            'content' => $content,
            'url' => $extractedUrl,
            'image_urls' => !empty($imageUrls) ? $imageUrls : null,
        ]);
        
        Log::info("記事更新完了: ID = {$article->id}, URL = " . ($article->fresh()->url ?? 'null'));

        return redirect()->route('articles.index')
            ->with('success', '記事を更新しました');
    }

    /**
     * 記事を削除
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route('articles.index')
            ->with('success', '記事を削除しました');
    }

    /**
     * HTMLから本文部分を抽出（非推奨・後方互換性のため残す）
     */
    private function extractContentFromHtml(string $html): string
    {
        $result = $this->extractContentAndImagesFromHtml($html);
        return $result['content'];
    }

    /**
     * HTMLから本文部分と画像URLを抽出（改行保持、画像内テキスト抽出対応）
     */
    private function extractContentAndImagesFromHtml(string $html): array
    {
        $imageUrls = [];
        $imagePositions = []; // 画像の出現位置を記録

        try {
            $crawler = new Crawler($html);
            
            // 画像URLを抽出（全体から）
            $crawler->filter('img')->each(function (Crawler $img) use (&$imageUrls, &$imagePositions) {
                // src属性を優先、なければdata-src、data-lazy-srcも確認
                $src = $img->attr('src') 
                    ?? $img->attr('data-src') 
                    ?? $img->attr('data-lazy-src')
                    ?? null;
                
                if ($src) {
                    // 相対パスを絶対パスに変換（必要に応じて）
                    $absoluteUrl = $this->convertToAbsoluteUrl($src);
                    if ($absoluteUrl && !in_array($absoluteUrl, $imageUrls)) {
                        $imageUrls[] = $absoluteUrl;
                        // 画像の出現順序を記録（後で本文に挿入するため）
                        $imagePositions[] = [
                            'url' => $absoluteUrl,
                            'alt' => $img->attr('alt') ?? '',
                        ];
                    }
                }
                
                // srcset属性も確認（レスポンシブ画像）
                $srcset = $img->attr('srcset');
                if ($srcset) {
                    // srcset="url1 1x, url2 2x" のような形式からURLを抽出
                    preg_match_all('/(\S+)(?:\s+\d+x)?,?/i', $srcset, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $url) {
                            $url = trim($url);
                            $absoluteUrl = $this->convertToAbsoluteUrl($url);
                            if ($absoluteUrl && !in_array($absoluteUrl, $imageUrls)) {
                                $imageUrls[] = $absoluteUrl;
                                $imagePositions[] = [
                                    'url' => $absoluteUrl,
                                    'alt' => $img->attr('alt') ?? '',
                                ];
                            }
                        }
                    }
                }
            });
            
            // 抽出対象の優先順位で試行
            $selectors = [
                'article',
                '.post_content',
                '#main_content',
                'main',
                '.entry-content',
                'body',
            ];
            
            foreach ($selectors as $selector) {
                try {
                    $element = $crawler->filter($selector);
                    if ($element->count() > 0) {
                        // HTMLを取得
                        $htmlContent = $element->html();
                        $cleanCrawler = new Crawler($htmlContent);
                        
                        // 不要な要素を削除
                        $tagsToRemove = ['script', 'style', 'nav', 'header', 'footer', 'aside'];
                        foreach ($tagsToRemove as $tag) {
                            $cleanCrawler->filter($tag)->each(function (Crawler $node) {
                                $nodeNode = $node->getNode(0);
                                if ($nodeNode && $nodeNode->parentNode) {
                                    $nodeNode->parentNode->removeChild($nodeNode);
                                }
                            });
                        }
                        
                        // 改行を保持してテキストを抽出
                        $content = $this->extractTextWithLineBreaks($cleanCrawler);
                        
                        // ブラウザコンソールエラーメッセージを除去
                        $content = $this->removeConsoleErrors($content);
                        
                        // 画像内テキストを抽出して本文に挿入（非同期処理でタイムアウトを回避）
                        if (!empty($imagePositions)) {
                            $content = $this->insertImageTexts($content, $imagePositions, $cleanCrawler);
                        }
                        
                        return [
                            'content' => $content,
                            'image_urls' => $imageUrls,
                        ];
                    }
                } catch (\Exception $e) {
                    // セレクターが見つからない場合は次を試行
                    continue;
                }
            }
        } catch (\Exception $e) {
            // パースに失敗した場合はログに記録
            Log::warning("HTML抽出に失敗しました: " . $e->getMessage());
        }
        
        // どれも見つからない場合はstrip_tagsでフォールバック（改行保持）
        $content = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</li>'], "\n", $html));
        $content = preg_replace('/\n{3,}/', "\n\n", $content); // 連続する改行を2つまでに制限
        
        // 空行を削除
        $content = $this->removeEmptyLines($content);
        
        return [
            'content' => trim($content),
            'image_urls' => $imageUrls,
        ];
    }
    
    /**
     * 改行を保持してテキストを抽出（画像をマーカーに置き換え）
     */
    private function extractTextWithLineBreaks(Crawler $crawler): string
    {
        // HTMLを取得
        $html = $crawler->html();
        
        // 画像タグを[画像:インデックス]マーカーに置き換え（位置を記録するため）
        $imageIndex = 0;
        $html = preg_replace_callback('/<img[^>]*>/i', function ($matches) use (&$imageIndex) {
            return "\n[画像:" . ($imageIndex++) . "]\n";
        }, $html);
        
        // ブロック要素の終了タグの後に改行を追加
        $html = preg_replace('/<\/(p|div|h[1-6]|li|td|th|tr)>/i', "</$1>\n", $html);
        
        // <br>タグを改行に変換
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        
        // リスト項目の前に改行を追加
        $html = preg_replace('/<li[^>]*>/i', "\n", $html);
        
        // HTMLタグを削除（改行は保持）
        $text = strip_tags($html);
        
        // 連続する空白を1つに、連続する改行を2つまでに制限
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        // 空行を削除
        $text = $this->removeEmptyLines($text);
        
        return trim($text);
    }
    
    /**
     * ブラウザコンソールエラーメッセージを除去
     */
    private function removeConsoleErrors(string $content): string
    {
        // ブラウザコンソールエラーパターンを除去
        $patterns = [
            // Chrome拡張機能エラー
            '/chrome-extension:\/\/[^\s]+/i',
            // CORS エラー
            '/CORS エラー/i',
            '/Failed to load resource/i',
            '/net::ERR_[A-Z_]+/i',
            '/Uncaught \(in promise\)/i',
            '/Failed to fetch dynamically imported module/i',
            // コンソールメッセージ
            '/Injected CSS loaded successfully/i',
            '/コンソールに .* を表示する/i',
            // その他のブラウザエラー
            '/⊗/u',
            '/☑/u',
        ];
        
        foreach ($patterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }
        
        // 空行を削除（改行のみ、または空白文字のみの行）
        $content = $this->removeEmptyLines($content);
        
        // 連続する改行や空白を整理
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = preg_replace('/[ \t]{2,}/', ' ', $content);
        
        return trim($content);
    }
    
    /**
     * 空行を削除（改行のみ、または空白文字のみの行）
     */
    private function removeEmptyLines(string $content): string
    {
        // 行ごとに分割
        $lines = explode("\n", $content);
        $filteredLines = [];
        
        foreach ($lines as $line) {
            // 空白文字（スペース、タブなど）のみの行は削除
            $trimmedLine = trim($line);
            if ($trimmedLine !== '') {
                $filteredLines[] = $line;
            }
        }
        
        return implode("\n", $filteredLines);
    }
    
    /**
     * 画像内テキストを抽出して本文に挿入（画像マーカーの位置に挿入）
     * 注意: OCR処理は時間がかかるため、最大5秒のタイムアウトを設定
     */
    private function insertImageTexts(string $content, array $imagePositions, Crawler $crawler): string
    {
        // 画像ごとにOCRでテキストを抽出（最大3枚まで処理、タイムアウト対策）
        $imageTexts = [];
        $processedCount = 0;
        $maxImages = 3; // 処理する画像の最大数
        
        foreach ($imagePositions as $index => $imagePos) {
            // 最大数に達したら処理を停止
            if ($processedCount >= $maxImages) {
                Log::info("OCR処理: 最大処理数に達したため、残りの画像はスキップします");
                break;
            }
            
            try {
                // タイムアウトを短く設定（1画像あたり最大10秒）
                $startTime = microtime(true);
                
                // 画像をダウンロード
                $imageData = $this->imageService->downloadAndEncode($imagePos['url']);
                
                if ($imageData) {
                    // Gemini APIで画像内テキストを抽出（タイムアウト短縮）
                    $imageText = $this->geminiService->extractTextFromImage($imageData);
                    
                    $elapsedTime = microtime(true) - $startTime;
                    
                    if (!empty($imageText)) {
                        // 抽出されたテキストがブラウザエラーメッセージでないかチェック
                        if ($this->isValidOcrText($imageText)) {
                            $imageTexts[$index] = $imageText;
                            $processedCount++;
                            Log::info("OCR処理成功: 画像 {$index} - 処理時間: {$elapsedTime}秒");
                        } else {
                            Log::warning("OCR結果が無効なテキスト: 画像 {$index}");
                            $imageTexts[$index] = '';
                        }
                    } else {
                        $imageTexts[$index] = '';
                    }
                    
                    // 処理時間が長すぎる場合は次の画像をスキップ
                    if ($elapsedTime > 10) {
                        Log::warning("OCR処理時間が長すぎます: {$elapsedTime}秒");
                    }
                } else {
                    $imageTexts[$index] = '';
                }
            } catch (\Exception $e) {
                Log::warning("画像OCR抽出エラー: {$imagePos['url']} - {$e->getMessage()}");
                $imageTexts[$index] = '';
            }
        }
        
        // [画像:インデックス]マーカーを画像内テキストに置き換え
        $finalContent = preg_replace_callback(
            '/\[画像:(\d+)\]/',
            function ($matches) use ($imageTexts) {
                $index = (int)$matches[1];
                if (isset($imageTexts[$index]) && !empty($imageTexts[$index])) {
                    return "\n" . $imageTexts[$index] . "\n";
                }
                return ''; // テキストが抽出できなかった場合はマーカーを削除
            },
            $content
        );
        
        // 空行を削除
        $finalContent = $this->removeEmptyLines($finalContent);
        
        // 連続する改行を整理
        $finalContent = preg_replace('/\n{3,}/', "\n\n", $finalContent);
        
        return trim($finalContent);
    }
    
    /**
     * OCR抽出されたテキストが有効かどうかをチェック
     */
    private function isValidOcrText(string $text): bool
    {
        // ブラウザエラーメッセージのパターンが含まれていないかチェック
        $errorPatterns = [
            '/chrome-extension:/i',
            '/net::ERR_/i',
            '/Failed to load resource/i',
            '/CORS エラー/i',
            '/Uncaught/i',
            '/⊗/u',
        ];
        
        foreach ($errorPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return false;
            }
        }
        
        // テキストが空でないか
        if (trim($text) === '') {
            return false;
        }
        
        // 最低限の長さがあるか（短すぎる場合は無効）
        if (mb_strlen(trim($text)) < 3) {
            return false;
        }
        
        return true;
    }

    /**
     * 相対URLを絶対URLに変換（改善版）
     */
    private function convertToAbsoluteUrl(string $url): ?string
    {
        // 空文字の場合はnull
        if (empty(trim($url))) {
            return null;
        }

        // データURIの場合はnullを返す（Gemini APIでは使用しない）
        if (strpos($url, 'data:') === 0) {
            return null;
        }

        // 既に絶対URLの場合はそのまま返す
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // プロトコル相対URLの場合（//example.com/image.jpg）
        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }

        // SVGアンカーやフラグメントの場合はnull
        if (strpos($url, '#') === 0) {
            return null;
        }

        // 相対パスの場合（/path/to/image.jpg または path/to/image.jpg）
        // HTTPコンテキストがないため、絶対URLに変換できない場合はnull
        // ただし、先頭が/で始まる場合はドメインが不明なので無視
        if (strpos($url, '/') === 0) {
            // ルート相対パスの場合、保存はするが、実際のURLは不明
            // 将来的にベースURLを指定できるようにする
            return null;
        }

        // その他の相対パスも無視
        return null;
    }

    /**
     * Markdown本文からURLを抽出
     * 抽出パターン:
     * - `**URL**: https://...`
     * - `- URL: https://...`
     * - `URL: https://...`（行頭）
     * 
     * @param string $content
     * @return string|null
     */
    private function extractUrlFromContent(string $content): ?string
    {
        if (empty($content)) {
            Log::info("URL抽出: コンテンツが空です");
            return null;
        }

        // HTMLから直接URLを抽出（HTML自動抽出が有効な場合）
        if (strpos($content, '<a ') !== false || strpos($content, '<A ') !== false) {
            Log::info("URL抽出: HTMLリンクタグを検出しました");
            
            // パターン1: `<a href="https://...">`
            if (preg_match('/<a[^>]+href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i', $content, $matches)) {
                $url = trim($matches[1]);
                Log::info("URL抽出成功（HTMLリンクタグ）: {$url}");
                return $url;
            }
        }

        // 共通URL抽出パターン（URLの末尾の空白、改行、各種記号を除外）
        $urlPattern = '(https?:\/\/[^\s\r\n\)\]<>"\']+)';

        // パターン1: `**URL**: https://...` (Markdownのボールド表記)
        if (preg_match('/\*\*URL\*\*:\s*' . $urlPattern . '/i', $content, $matches)) {
            $url = trim($matches[1]);
            $url = preg_replace('/[\r\n\s]+.*$/', '', $url);
            $url = rtrim($url, '.,;:!?');
            Log::info("URL抽出成功（パターン1: **URL**:）: {$url}");
            return $url;
        }

        // パターン2: `- URL: https://...` (リスト形式)
        if (preg_match('/^-\s*URL:\s*' . $urlPattern . '/mi', $content, $matches)) {
            $url = trim($matches[1]);
            $url = preg_replace('/[\r\n\s]+.*$/', '', $url);
            $url = rtrim($url, '.,;:!?');
            Log::info("URL抽出成功（パターン2: - URL:）: {$url}");
            return $url;
        }

        // パターン3: `URL: https://...`（行頭）
        if (preg_match('/^URL:\s*' . $urlPattern . '/mi', $content, $matches)) {
            $url = trim($matches[1]);
            $url = preg_replace('/[\r\n\s]+.*$/', '', $url);
            $url = rtrim($url, '.,;:!?');
            Log::info("URL抽出成功（パターン3: URL: 行頭）: {$url}");
            return $url;
        }

        // パターン4: `URL: https://...`（行頭ではない場合も検索）
        if (preg_match('/URL:\s*' . $urlPattern . '/i', $content, $matches)) {
            $url = trim($matches[1]);
            $url = preg_replace('/[\r\n\s]+.*$/', '', $url);
            $url = rtrim($url, '.,;:!?');
            Log::info("URL抽出成功（パターン4: URL:）: {$url}");
            return $url;
        }

        // パターン5: HTML内のURLを直接検索（リンクタグ以外）
        if (preg_match('/\b(https?:\/\/[^\s<>"\']+)/i', $content, $matches)) {
            $url = trim($matches[1]);
            // 末尾の記号を除去
            $url = rtrim($url, '.,;:!?');
            Log::info("URL抽出成功（パターン5: 直接URL検索）: {$url}");
            return $url;
        }

        // デバッグ用: 内容の先頭500文字をログに記録
        $preview = mb_substr($content, 0, 500);
        $hasHtml = (strpos($content, '<') !== false) ? 'あり' : 'なし';
        $hasUrlKeyword = (stripos($content, 'URL') !== false) ? 'あり' : 'なし';
        Log::info("URL抽出失敗: パターンにマッチしませんでした。HTML: {$hasHtml}, URLキーワード: {$hasUrlKeyword}, 内容の先頭500文字: " . str_replace(["\r", "\n"], ['\\r', '\\n'], $preview));
        
        return null;
    }

    /**
     * HTMLから直接URLを抽出
     * 主にリンクタグ（<a href="">）からURLを抽出
     * 
     * @param string $html
     * @return string|null
     */
    private function extractUrlFromHtml(string $html): ?string
    {
        if (empty($html)) {
            return null;
        }

        $url = null;

        try {
            $crawler = new Crawler($html);
            
            // リンクタグからURLを抽出
            $crawler->filter('a')->each(function (Crawler $link) use (&$url) {
                if ($url !== null) {
                    return; // 既にURLが見つかっている場合は処理をスキップ
                }
                
                $href = $link->attr('href');
                if ($href && preg_match('/^https?:\/\//i', $href)) {
                    $url = trim($href);
                    Log::info("HTMLからURL抽出成功（リンクタグ）: {$url}");
                }
            });
            
            if ($url !== null) {
                return $url;
            }
        } catch (\Exception $e) {
            Log::warning("HTMLからのURL抽出でエラー: {$e->getMessage()}");
        }

        // リンクタグから見つからなかった場合、正規表現で直接検索
        if (preg_match('/<a[^>]+href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i', $html, $matches)) {
            $url = trim($matches[1]);
            Log::info("HTMLからURL抽出成功（正規表現）: {$url}");
            return $url;
        }

        return null;
    }
}
