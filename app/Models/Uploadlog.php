<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uploadlog extends Model
{
    use HasFactory;
    protected $table = 'uploadlogs';
    protected $fillable = [
        'createdby',
        'excelname',
        'docno',
        'created_at',
        'updated_at',
    ];
    public $timestamps = false; 
}
