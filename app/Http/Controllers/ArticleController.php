<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'auto_extract' => 'nullable|boolean',
        ]);

        $content = $validated['content'];

        // HTMLから本文を自動抽出する場合
        if ($request->boolean('auto_extract')) {
            $content = $this->extractContentFromHtml($content);
        }

        Article::create([
            'title' => $validated['title'],
            'content' => $content,
        ]);

        // 続けて登録ボタンが押された場合
        if ($request->input('continue')) {
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'auto_extract' => 'nullable|boolean',
        ]);

        $content = $validated['content'];

        // HTMLから本文を自動抽出する場合
        if ($request->boolean('auto_extract')) {
            $content = $this->extractContentFromHtml($content);
        }

        $article->update([
            'title' => $validated['title'],
            'content' => $content,
        ]);

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
     * HTMLから本文部分を抽出
     */
    private function extractContentFromHtml(string $html): string
    {
        try {
            $crawler = new Crawler($html);
            
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
                        
                        // テキストを抽出
                        return $cleanCrawler->text();
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
        
        // どれも見つからない場合はstrip_tagsでフォールバック
        return strip_tags($html);
    }
}
