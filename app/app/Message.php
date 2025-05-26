<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Skill;
use App\Review;
use App\User;

// app/Models/Message.php
class Message extends Model
{
    protected $fillable = ['matching_id', 'sender_id','receiver_id', 'content','sent_at'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
     public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
