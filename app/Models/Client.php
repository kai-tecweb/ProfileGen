<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'memo',
        'questionnaire_text',
        'answers_text',
    ];

    /**
     * クライアントに関連する提案
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }
}
