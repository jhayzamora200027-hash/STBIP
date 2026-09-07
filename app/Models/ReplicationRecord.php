<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReplicationRecord extends Model
{
    protected $fillable = [
        'social_technology_title',
        'user_id',
        'redirect_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}