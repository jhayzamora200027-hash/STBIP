<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialTechnologyTitle extends Model
{
    use HasFactory;

    protected $table = 'social_technology_titles';

    protected $fillable = [
        'title',
        'createdby',
        'updatedby',
    ];
}
