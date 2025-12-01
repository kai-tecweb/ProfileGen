<?php

namespace App\Http\Controllers\AdminV2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * ログイン画面を表示
     */
    public function showLoginForm()
    {
        // 既にログイン済みの場合は相談履歴管理画面にリダイレクト
        if (Session::get('adminv2_authenticated')) {
            return redirect()->route('admin.consultations.index');
        }

        return Inertia::render('AdminV2/Login');
    }

    /**
     * ログイン処理
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $correctPassword = env('ADMIN_PASSWORD', 'oz789');

        if ($validated['password'] === $correctPassword) {
            Session::put('adminv2_authenticated', true);
            Session::put('adminv2_authenticated_at', now());

            return redirect()->route('admin.consultations.index')
                ->with('success', 'ログインしました');
        }

        return back()->withErrors([
            'password' => 'パスワードが正しくありません',
        ])->withInput();
    }

    /**
     * ログアウト処理
     */
    public function logout()
    {
        Session::forget('adminv2_authenticated');
        Session::forget('adminv2_authenticated_at');

        return redirect()->route('admin.login')
            ->with('success', 'ログアウトしました');
    }
}

