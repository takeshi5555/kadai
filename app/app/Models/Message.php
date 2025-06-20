<?php

namespace App\Models; // または App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Message extends Model
{
    use HasFactory;
    // ★ ここに receiver_id と sent_at を追加
    protected $fillable = ['matching_id', 'sender_id', 'receiver_id', 'content',  'read_at','sent_at', 'created_at', 'updated_at'];

    protected $dates = [
        'read_at', // ★ここに追加★
    ];
     protected $casts = [
        'read_at' => 'datetime', // ★ここに追加: datetime型としてキャスト★
        // 'sent_at' => 'datetime', // もしsent_atも使っているならここに追加
    ];
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
        public function matching()
    {
        return $this->belongsTo(Matching::class);
    }
}