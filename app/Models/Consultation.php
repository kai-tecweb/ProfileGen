<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'user_id',
        'student_id',
        'is_corrected',
    ];

    protected $casts = [
        'is_corrected' => 'boolean',
    ];

    /**
     * ユーザー（管理者）とのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 生徒とのリレーション
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 修正履歴とのリレーション
     */
    public function corrections(): HasMany
    {
        return $this->hasMany(Correction::class);
    }
}
