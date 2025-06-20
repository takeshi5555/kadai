<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth; // Auth ファサードを使用
use App\Models\Skill;
use App\Models\Review; // Review モデルを使用
use App\Models\User; // User モデルを使用

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
        // ログインユーザーが書いたレビュー
        return $this->hasOne(Review::class, 'matching_id')
                    ->where('reviewer_id', auth()->id());
    }


    public function partnerReview() // 相手（ログインユーザーに対して）が書いたレビュー
    {
        // 相手がレビューを書いた、かつ、ログインユーザーがレビューの受け取り手であるレビューを取得
        return $this->hasOne(Review::class, 'matching_id')
                    ->where('reviewee_id', Auth::id()); // ★ここを修正★
    }

    // 申請者ユーザーへのリレーション
    public function applicantUser()
    {
        return $this->hasOneThrough(User::class, Skill::class,
            'id', // SkillテーブルのID
            'id', // UserテーブルのID
            'offering_skill_id', // Matchingテーブルの外部キー (SkillのID)
            'user_id' // Skillテーブルの外部キー (UserのID)
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

    // skill() は offeringSkill または receivingSkill を指すため、明確さを考慮すると不要かもしれません
    // もし特定の目的がある場合は残してください
    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function offerUser()
    {
        return $this->hasOneThrough(
            User::class,        // 最終的に取得したいモデル
            Skill::class,       // 中間モデル
            'id',               // Skillモデルのローカルキー (id)
            'id',               // Userモデルのローカルキー (id)
            'offering_skill_id',// Matchingモデルのローカルキー (matching.offering_skill_id)
            'user_id'           // Skillモデルの外部キー (skill.user_id)
        );
    }


    public function requestUser()
    {
        return $this->hasOneThrough(
            User::class,
            Skill::class,
            'id',
            'id',
            'receiving_skill_id',
            'user_id'
        );
    }


    public function review()
    {
        // これはマッチングに紐づく全てのレビューを取得するリレーションとして残せますが、
        // myReview() や partnerReview() の方が特定のレビューを取得するのには適しています。
        return $this->hasOne(Review::class, 'matching_id');
    }

    // public function reviewFromPartner() は partnerReview() と重複するため削除します ★削除★
    // public function reviewFromPartner()
    // {
    //     return $this->hasOne(Review::class, 'matching_id')->where('reviewee_id', Auth::id());
    // }

    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case 0: return '保留中';
            case 1: return '承認済み';
            case 2: return '完了';
            case 3: return 'キャンセル';
            case 4: return '拒否';
            default: return '不明';
        }
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'matching_id');
    }

public function messages()
    {
        return $this->hasMany(Message::class, 'matching_id');
    }

}