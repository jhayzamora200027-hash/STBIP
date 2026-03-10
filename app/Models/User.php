<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'middlename',
        'lastname',
        'email',
        'password',
        'user_id',
        'active',
        'usergroup',
        'approvalstatus',
        'phonenumber',
        'gender',
        'address',
        'profile_picture_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        $fullName = trim(collect([
            $this->firstname,
            $this->middlename,
            $this->lastname,
        ])->filter()->implode(' '));

        return $fullName !== '' ? $fullName : ($this->name ?: 'User');
    }

    public function getInitialsAttribute(): string
    {
        $parts = collect(explode(' ', $this->display_name))
            ->filter()
            ->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $parts !== '' ? $parts : 'U';
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        return $this->profile_picture_path
            ? asset('storage/' . ltrim($this->profile_picture_path, '/'))
            : null;
    }

}
