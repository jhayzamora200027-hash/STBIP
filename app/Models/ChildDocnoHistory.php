<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildDocnoHistory extends Model
{
    protected $table = 'child_docno_histories';

    protected $fillable = [
        'gallery_child_id',
        'gallery_card_id',
        'docno',
        'previous_docno',
        'created_by',
        'notes',
    ];

    public function child()
    {
        return $this->belongsTo(GalleryChild::class, 'gallery_child_id');
    }

    public function mother()
    {
        return $this->belongsTo(GalleryCard::class, 'gallery_card_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'user_id');
    }
}
