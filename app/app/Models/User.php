<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','role','is_banned','google_id','remember_token',
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
        'is_banned' => 'boolean', 
    ];

    
    public function skills()
    {
        return $this->hasMany(Skill::class, 'user_id'); // ユーザーが登録したスキル
    }



     // ========== 通知カウント用アクセサここから ==========

    /**
     * 未読メッセージ数を取得するアクセサ
     *
     * @return int
     */
    public function getUnreadMessageCountAttribute()
    {
        // 'receivedMessages' リレーションを使用し、'read_at' が null のメッセージをカウント
        return $this->receivedMessages()->whereNull('read_at')->count();
    }

    /**
     * 未確認のマッチング数を取得するアクセサ
     *
     * @return int
     */
    public function getUnconfirmedMatchingCountAttribute()
    {
        // ユーザーが提供したスキルに関連する未確認マッチング
        $offeredPendingCount = $this->offeredMatchings()->where('status', 0)->count();

        // ユーザーがリクエストしたスキルに関連する未確認マッチング
        $requestedPendingCount = $this->requestedMatchings()->where('status', 0)->count();

        // 両方を合計して返す
        return $offeredPendingCount + $requestedPendingCount;
    }

    // ========== 通知カウント用アクセサここまで ==========
    // マッチング履歴のリレーション
    // SQLを見ると、matchingsテーブルには`offer_user_id`と`request_user_id`があります。
    // どちらのユーザーから見てもマッチング履歴として取得できるようにリレーションを定義します。
       public function offeredMatchings()
    {
        return $this->hasManyThrough(
            Matching::class,      // 最終的に取得したいモデル
            Skill::class,         // 中間モデル
            'user_id',            // Skillテーブルの外部キー（UserのIDを指す）
            'offering_skill_id',  // Matchingテーブルの外部キー（SkillのIDを指す）
            'id',                 // Userテーブルのローカルキー
            'id'                  // Skillテーブルのローカルキー
        );
    }

    // ユーザーがリクエスト側として関わるマッチング (ユーザーがリクエストするスキルを含むマッチング)
    public function requestedMatchings()
    {
        return $this->hasManyThrough(
            Matching::class,
            Skill::class,
            'user_id',
            'receiving_skill_id',
            'id',
            'id'
        );
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

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }


    

    // ユーザーのロールをチェックするヘルパーメソッド (推奨)
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }
 
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    // このユーザーがreviewer_idとして書いたレビュー
    public function reviewsWritten()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }
    


    // Receiving側
    public function receivedMatchings()
    {
        return $this->hasManyThrough(Matching::class, Skill::class, 'user_id', 'receiving_skill_id', 'id', 'id');
    }
    public function receivedReports()
    {
        // reported_user_id は、reportsテーブルで通報を受けたユーザーのIDを示すカラム
        return $this->hasMany(Report::class, 'reported_user_id');
    }
    public function warnings()
    {
        // user_id は、user_warningsテーブルで警告を受けたユーザーのIDを示すカラム
        return $this->hasMany(UserWarning::class, 'user_id');
    }

    public function getAverageRatingReceivedAttribute()
{
    return $this->reviewsReceived()->avg('rating');
}
}

