<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Client;
use App\Models\Proposal;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * ダッシュボードを表示
     */
    public function index(): Response
    {
        $stats = [
            'articles_count' => Article::count(),
            'clients_count' => Client::count(),
            'proposals_count' => Proposal::count(),
        ];

        $recentProposals = Proposal::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentProposals' => $recentProposals,
        ]);
    }
}
