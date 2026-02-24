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
        // store the document number selected when the base file is set/updated
        'docselected',
        'created_at',
        'updated_at',
    ];
}
