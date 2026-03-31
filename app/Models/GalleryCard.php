<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryCard extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'icon_class',
        'url',
        'docno',
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

    
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'user_id');
    }

   
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by', 'user_id');
    }

    
    public function children()
    {
        return $this->hasMany(\App\Models\GalleryChild::class, 'gallery_card_id');
    }
}
