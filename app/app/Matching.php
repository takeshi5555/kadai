<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Skill;
use App\Review;
use App\User;


class Matching extends Model
{
    protected $fillable = [
        'offering_skill_id', 'receiving_skill_id', 'status', 'scheduled_at'
    ];

    public function offeringSkill()
    {
    return $this->belongsTo(Skill::class, 'offering_skill_id');
    }

    public function receivingSkill()

    {
    return $this->belongsTo(Skill::class, 'receiving_skill_id');
    }


    public function myReview()
    {
        return $this->hasOne(\App\Review::class)
        ->where('reviewer_id', auth()->id());
    }

    // レビューされた側が自分になるレビューを取得
    public function partnerReview()
    {
        return $this->hasOne(Review::class, 'matching_id')->where('reviewee_id', Auth::id());
    }

    // 申請者ユーザーへのリレーション
    public function applicantUser()
    {
        return $this->hasOneThrough(User::class, Skill::class,
            'id', 
            'id',
            'offering_skill_id',
            'user_id' 
        );
    }

    // 受領者ユーザーへのリレーション
    public function recipientUser()
    {
        return $this->hasOneThrough(User::class, Skill::class,
            'id',
            'id',
            'receiving_skill_id',
            'user_id'
        );
    }

        public function skill()
    {
        return $this->belongsTo(Skill::class);
    }


}
