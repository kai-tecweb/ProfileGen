<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Inertia\Inertia;
use Inertia\Response;

class ConsultationKnowledgeController extends Controller
{
    /**
     * 相談チャット用ナレッジ一覧を表示
     */
    public function index(): Response
    {
        $articles = Article::orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/ConsultationKnowledge/Index', [
            'articles' => $articles,
        ]);
    }
}

