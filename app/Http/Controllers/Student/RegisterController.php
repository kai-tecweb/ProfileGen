<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * 申請ページを表示
     */
    public function create(): Response
    {
        return Inertia::render('Student/Register');
    }

    /**
     * 申請を保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'discord_name' => ['required', 'string', 'max:255', 'unique:students,discord_name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:students,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'discord_name.required' => 'Discord名を入力してください',
            'discord_name.unique' => 'このDiscord名は既に登録されています',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '正しいメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に登録されています',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
        ]);

        // 申請データを保存（statusはデフォルトでpending）
        Student::create([
            'discord_name' => $validated['discord_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'pending',
            'student_status' => 'active',
        ]);

        return redirect()->route('student.register.success')
            ->with('success', '申請を受け付けました。管理者の承認をお待ちください。');
    }

    /**
     * 申請完了ページを表示
     */
    public function success(): Response
    {
        return Inertia::render('Student/RegisterSuccess');
    }
}
