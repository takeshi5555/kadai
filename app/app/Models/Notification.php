<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model // ここが Notification クラスになっているか
{
    use HasFactory;

    // ★ ここに fillsable または guarded プロパティを追加してください ★
    // mass assignment exception を避けるため
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    // リレーションがある場合
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}