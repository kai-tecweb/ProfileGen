<?php

namespace App\Http\Controllers\Student;

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
    public function showLoginForm(): Response
    {
        // 既にログイン済みの場合は相談チャット画面にリダイレクト
        if (Session::get('student_authenticated')) {
            return Inertia::location(route('student.consultations.index'));
        }

        return Inertia::render('Student/Login');
    }

    /**
     * ログイン処理
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $correctPassword = env('STUDENT_PASSWORD', '47446');

        if ($validated['password'] === $correctPassword) {
            Session::put('student_authenticated', true);
            Session::put('student_authenticated_at', now());

            return redirect()->route('student.consultations.index')
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
        Session::forget('student_authenticated');
        Session::forget('student_authenticated_at');

        return redirect()->route('student.login')
            ->with('success', 'ログアウトしました');
    }
}

