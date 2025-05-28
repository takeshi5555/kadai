<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Skill extends Model
{

    use HasFactory;
    protected $fillable = [
        'user_id', 'title', 'category', 'description'
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
}
