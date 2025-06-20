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


    public function offeredMatchings()
    {
        return $this->hasMany(Matching::class, 'offering_skill_id');
    }

    // このスキルが受講されるマッチング
    public function receivedMatchings()
    {
        return $this->hasMany(Matching::class, 'receiving_skill_id');
    }

     public function reviews()
    {
        // このスキルが提供側または受領側となっているマッチングのIDを全て取得
        $offeringMatchingIds = $this->offeredMatchings()->pluck('id');
        $receivingMatchingIds = $this->receivedMatchings()->pluck('id');

        // 両方のIDを結合し、重複を排除
        $allRelevantMatchingIds = $offeringMatchingIds->merge($receivingMatchingIds)->unique();

        // 取得したマッチングIDに紐づくレビューを取得するクエリを返す
        return Review::whereIn('matching_id', $allRelevantMatchingIds);
    }
}
