<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StsAttachment extends Model
{
    protected $table = 'stsattachment';

    protected $fillable = [
        'region',
        'province',
        'municipality',
        'title',
        'year_of_moa',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'created_by',
        'action',
    ];
}
