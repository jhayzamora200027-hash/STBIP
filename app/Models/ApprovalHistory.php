<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'applicant_name',
        'applicant_email',
        'action',
        'reviewed_by_name',
        'reviewed_by_email',
        'assigned_usergroup',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}