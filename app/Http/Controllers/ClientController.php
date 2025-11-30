<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * クライアント一覧を表示
     */
    public function index(): Response
    {
        $clients = Client::orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    /**
     * クライアント登録フォームを表示
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * クライアントを保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'questionnaire_text' => 'nullable|string',
            'answers_text' => 'nullable|string',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'クライアントを登録しました');
    }

    /**
     * クライアント詳細を表示
     */
    public function show(Client $client): Response
    {
        $client->load('proposals');

        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    /**
     * クライアント編集フォームを表示
     */
    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * クライアントを更新
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'questionnaire_text' => 'nullable|string',
            'answers_text' => 'required|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'クライアントを更新しました');
    }

    /**
     * クライアントを削除
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'クライアントを削除しました');
    }
}
