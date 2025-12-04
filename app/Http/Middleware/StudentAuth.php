<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class StudentAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $studentId = Session::get('student_id');

        if (!$studentId) {
            return redirect()->route('student.login');
        }

        // 生徒が存在し、ログイン可能かチェック
        $student = Student::find($studentId);
        if (!$student || !$student->canLogin()) {
            Session::forget('student_id');
            Session::forget('student_discord_name');
            Session::forget('student_authenticated_at');

            return redirect()->route('student.login')
                ->withErrors(['discord_name' => 'このアカウントはログインできません。']);
        }

        return $next($request);
    }
}
