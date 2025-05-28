<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function skills()
    {
        return $this->hasMany(Skill::class, 'user_id'); // ユーザーが登録したスキル
    }

    // マッチング履歴のリレーション
    // SQLを見ると、matchingsテーブルには`offer_user_id`と`request_user_id`があります。
    // どちらのユーザーから見てもマッチング履歴として取得できるようにリレーションを定義します。
    public function offeredMatchings()
    {
        return $this->hasMany(Matching::class, 'offer_user_id');
    }

    public function requestedMatchings()
    {
        return $this->hasMany(Matching::class, 'request_user_id');
    }

    // 両方のマッチングを結合して取得するアクセサ（オプション）
    public function getAllMatchingsAttribute()
    {
        return $this->offeredMatchings->merge($this->requestedMatchings);
    }

    // レビューのリレーション
    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    // メッセージのリレーション
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}

