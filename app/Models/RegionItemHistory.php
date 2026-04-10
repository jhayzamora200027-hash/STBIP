<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionItemHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_item_id',
        'region_id',
        'region_name',
        'st_title',
        'province',
        'city',
        'updated_by',
        'action',
        'update_row',
    ];

    public function regionItem(): BelongsTo
    {
        return $this->belongsTo(RegionItem::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}