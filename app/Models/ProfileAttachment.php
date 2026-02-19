<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileAttachment extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
