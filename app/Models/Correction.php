<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Correction extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'wrong_answer',
        'correct_answer',
        'corrected_by',
    ];

    /**
     * 相談とのリレーション
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
