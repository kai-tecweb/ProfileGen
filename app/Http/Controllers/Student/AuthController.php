<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        if (Session::get('student_id')) {
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
            'discord_name' => 'required|string',
            'password' => 'required|string',
        ], [
            'discord_name.required' => 'Discord名を入力してください',
            'password.required' => 'パスワードを入力してください',
        ]);

        // Discord名で生徒を検索
        $student = Student::where('discord_name', $validated['discord_name'])->first();

        if (!$student) {
            return back()->withErrors([
                'discord_name' => 'Discord名またはパスワードが正しくありません',
            ])->withInput();
        }

        // パスワード確認
        if (!Hash::check($validated['password'], $student->password)) {
            return back()->withErrors([
                'discord_name' => 'Discord名またはパスワードが正しくありません',
            ])->withInput();
        }

        // 承認済みかチェック
        if (!$student->isApproved()) {
            return back()->withErrors([
                'discord_name' => 'このアカウントはまだ承認されていません。管理者の承認をお待ちください。',
            ])->withInput();
        }

        // ログイン可能かチェック（追放されていないか）
        if (!$student->canLogin()) {
            return back()->withErrors([
                'discord_name' => 'このアカウントはログインできません。',
            ])->withInput();
        }

        // セッションに生徒IDを保存
        Session::put('student_id', $student->id);
        Session::put('student_discord_name', $student->discord_name);
        Session::put('student_authenticated_at', now());

        return redirect()->route('student.consultations.index')
            ->with('success', 'ログインしました');
    }

    /**
     * ログアウト処理
     */
    public function logout()
    {
        Session::forget('student_id');
        Session::forget('student_discord_name');
        Session::forget('student_authenticated_at');

        return redirect()->route('student.login')
            ->with('success', 'ログアウトしました');
    }
}

