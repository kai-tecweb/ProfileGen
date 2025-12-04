<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'discord_name',
        'email',
        'password',
        'status',
        'student_status',
        'approved_at',
        'approved_by',
        'memo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 承認した管理者とのリレーション
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 相談履歴とのリレーション
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * 承認済みかどうか
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * ログイン可能かどうか
     */
    public function canLogin(): bool
    {
        return $this->isApproved() && $this->student_status !== 'banned';
    }
}
