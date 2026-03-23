<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialTechnologyTitle extends Model
{
    use HasFactory;

    protected $table = 'social_technology_titles';

    protected $fillable = [
        'createdby',
        'updatedby',
        'sector',
        'laws_and_issuances',
        'social_technology',
        'description',
        'objectives',
        'components',
        'pilot_areas',
        'year_implemented',
        'status_remarks',
        'resolution',
        'guidelines',
        'program_manual_outline',
        'information_systems_developed',
        'session_guide_key_topics',
        'training_manual_outline',
    ];
}
