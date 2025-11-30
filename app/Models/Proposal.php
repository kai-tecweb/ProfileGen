<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'x_profile',
        'instagram_profile',
        'coconala_profile',
        'product_design',
    ];

    /**
     * 提案のクライアント
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
