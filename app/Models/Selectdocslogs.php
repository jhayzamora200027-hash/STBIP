<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Selectdocslogs extends Model
{
    protected $table = 'selectdocslogs';
    protected $fillable = [
        'createdby',
        'excelname',
        'actionlogs',
        'docselected',
        'created_at',
        'updated_at',
    ];
}
