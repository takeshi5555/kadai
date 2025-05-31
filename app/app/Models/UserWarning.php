<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'report_id',
        'message',
        'type',
        'warned_at',
    ];

    /**
     * 警告を受けたユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 警告を発行した管理者を取得
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id'); // Userモデルが管理者も含む前提
    }

    /**
     * 関連する通報を取得
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}