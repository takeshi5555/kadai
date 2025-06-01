<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Skill;
use App\Models\Review;
use App\Models\User;


class Matching extends Model
{
    protected $fillable = [
        'offering_skill_id', 'receiving_skill_id', 'status', 'scheduled_at'
    ];


    protected $casts = [
        'scheduled_at' => 'datetime',
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

    public function offerUser()
    {
        // offeringSkill() の所有者 (user_id) を取得
        return $this->hasOneThrough(
            User::class,     // 最終的に取得したいモデル
            Skill::class,    // 中間モデル
            'id',            // Skillモデルのローカルキー (id)
            'id',            // Userモデルのローカルキー (id)
            'offering_skill_id', // Matchingモデルのローカルキー (matching.offering_skill_id)
            'user_id'        // Skillモデルの外部キー (skill.user_id)
        );
    }

    // スキルリクエスト者 (receivingSkillを介してユーザーを取得)
    public function requestUser()
    {
        // receivingSkill() の所有者 (user_id) を取得
        return $this->hasOneThrough(
            User::class,
            Skill::class,
            'id',
            'id',
            'receiving_skill_id',
            'user_id'
        );
    }

    // このマッチングに紐づくレビュー（1対1の関係）
    public function review()
    {
        return $this->hasOne(Review::class, 'matching_id');
    }

    public function reviewFromPartner() 
    {
        return $this->hasOne(Review::class, 'matching_id')->where('reviewee_id', Auth::id());
    }

        public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case 0: return '保留中';
            case 1: return '承認済み';
            case 2: return '完了';
            case 3: return 'キャンセル';
            default: return '不明';
        }
    }
     public function reviews()
    {
        return $this->hasMany(Review::class, 'matching_id');
    }

}
