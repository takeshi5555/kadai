<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Skill extends Model
{

    use HasFactory;
    protected $fillable = [
        'user_id', 'title', 'category', 'description','image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function matchings()
    {
        return $this->hasMany(Matching::class); // スキルに対するマッチング
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function offeredMatchings()
    {
        return $this->hasMany(Matching::class, 'offering_skill_id');
    }

    // このスキルが受講されるマッチング
    public function receivedMatchings()
    {
        return $this->hasMany(Matching::class, 'receiving_skill_id');
    }
}
