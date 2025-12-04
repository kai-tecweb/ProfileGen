<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Student\ConsultationController as StudentConsultationController;
use App\Http\Controllers\AdminV2\AuthController as AdminV2AuthController;
use App\Http\Controllers\AdminV2\ConsultationController as AdminV2ConsultationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // クライアント管理
    Route::resource('clients', ClientController::class);
    Route::post('/clients/{client}/proposals/generate', [ProposalController::class, 'generate'])
        ->name('clients.proposals.generate');

    // 提案管理
    Route::get('/proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
    Route::get('/proposals/{proposal}/edit', [ProposalController::class, 'edit'])->name('proposals.edit');
    Route::put('/proposals/{proposal}', [ProposalController::class, 'update'])->name('proposals.update');
    Route::delete('/proposals/{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');

            // 質問マスター管理
            // カスタムルートをリソースルートより前に定義（ルートマッチングの優先順位のため）
            Route::post('/questions/generate', [QuestionController::class, 'generate'])->name('questions.generate');
            Route::post('/questions/regenerate', [QuestionController::class, 'regenerate'])->name('questions.regenerate');
            Route::resource('questions', QuestionController::class);

            // プロフィール
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 学生エリア（パスワード認証）
Route::prefix('student')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('student.login');
    Route::post('/login', [StudentAuthController::class, 'login']);
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('student.logout');

    Route::middleware([\App\Http\Middleware\StudentAuth::class])->group(function () {
        Route::get('/consultations', [StudentConsultationController::class, 'index'])->name('student.consultations.index');
        Route::post('/consultations', [StudentConsultationController::class, 'store'])->name('student.consultations.store');
    });
});

// 管理エリア（パスワード認証）
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminV2AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminV2AuthController::class, 'login']);
    Route::post('/logout', [AdminV2AuthController::class, 'logout'])->name('admin.logout');

    Route::middleware([\App\Http\Middleware\AdminV2Auth::class])->group(function () {
        Route::get('/consultations', [AdminV2ConsultationController::class, 'index'])->name('admin.consultations.index');
        Route::post('/consultations/{consultation}/correct', [AdminV2ConsultationController::class, 'correct'])->name('admin.consultations.correct');
        Route::post('/consultations/{consultation}/notify', [AdminV2ConsultationController::class, 'notifyDiscord'])->name('admin.consultations.notify');
        
        // 記事管理（ナレッジ管理）
        Route::resource('articles', ArticleController::class);
    });
});

require __DIR__.'/auth.php';
