<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryChild extends Model
{
    protected $table = 'gallery_children';

    protected $fillable = [
        'gallery_card_id',
        'parent_child_id',
        'title',
        'description',
        'url',
        'docno',
        'is_mother',
        'is_active',
        'status',
        'created_by',
        'updated_by',
    ];

    public function isCompleted()
    {
        return (string) ($this->status ?? '') === 'Completed';
    }

    public function isOngoing()
    {
        return (string) ($this->status ?? '') === 'On going';
    }

    public function parent()
    {
        return $this->belongsTo(GalleryCard::class, 'gallery_card_id');
    }

    public function parentChild()
    {
        return $this->belongsTo(self::class, 'parent_child_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_child_id');
    }

    public function histories()
    {
        return $this->hasMany(ChildDocnoHistory::class, 'gallery_child_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by', 'user_id');
    }
}
