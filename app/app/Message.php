<?php

namespace App; // または App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Message extends Model
{
    // ★ ここに receiver_id と sent_at を追加
    protected $fillable = ['matching_id', 'sender_id', 'receiver_id', 'content', 'sent_at', 'created_at', 'updated_at'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}