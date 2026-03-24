<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'title',
        'province',
        'municipality',
        'with_expr',
        'with_moa',
        'year_of_moa',
        'with_res',
        'year_of_resolution',
        'included_aip',
        'with_adopted',
        'with_replicated',
        'status',
        'inactive_status',
        'inactive_remarks',
        'createdby',
        'updatedby',
    ];

    protected $casts = [
        'with_expr' => 'boolean',
        'with_moa' => 'boolean',
        'with_res' => 'boolean',
        'included_aip' => 'boolean',
        'with_adopted' => 'boolean',
        'with_replicated' => 'boolean',
        'year_of_moa' => 'integer',
        'year_of_resolution' => 'integer',
        'inactive_remarks' => 'string',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}