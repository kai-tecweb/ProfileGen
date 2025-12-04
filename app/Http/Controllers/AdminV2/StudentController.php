<?php

namespace App\Http\Controllers\AdminV2;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * 申請待ち一覧を表示
     */
    public function pending(): Response
    {
        $pendingStudents = Student::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('AdminV2/Students/Pending', [
            'students' => $pendingStudents,
        ]);
    }

    /**
     * 生徒一覧を表示
     */
    public function index(): Response
    {
        $students = Student::with('approver')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('AdminV2/Students/Index', [
            'students' => $students,
        ]);
    }

    /**
     * 生徒を承認
     */
    public function approve(Request $request, Student $student)
    {
        if ($student->status !== 'pending') {
            return back()->withErrors(['error' => 'この申請は既に処理済みです。']);
        }

        $student->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id() ?? null, // セッション認証の場合はnull
        ]);

        return redirect()->route('admin.students.pending')
            ->with('success', '生徒を承認しました。');
    }

    /**
     * 生徒申請を却下
     */
    public function reject(Request $request, Student $student)
    {
        if ($student->status !== 'pending') {
            return back()->withErrors(['error' => 'この申請は既に処理済みです。']);
        }

        $student->update([
            'status' => 'rejected',
        ]);

        return redirect()->route('admin.students.pending')
            ->with('success', '申請を却下しました。');
    }

    /**
     * 生徒の状況を更新
     */
    public function updateStatus(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_status' => ['required', 'in:active,inactive,banned,no_contact'],
            'memo' => ['nullable', 'string', 'max:1000'],
        ]);

        $student->update($validated);

        return redirect()->route('admin.students.index')
            ->with('success', '生徒の状況を更新しました。');
    }

    /**
     * 生徒詳細を表示
     */
    public function show(Student $student): Response
    {
        $student->load(['approver', 'consultations']);

        return Inertia::render('AdminV2/Students/Show', [
            'student' => $student,
        ]);
    }
}
