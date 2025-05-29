<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{

    use HasFactory;

    protected $fillable = [
        'matching_id',
        'reviewer_id',
        'reviewee_id',
        'rating',
        'comment',
    ];

    public function matching()
    {
        return $this->belongsTo(Matching::class, 'matching_id');
    }

     public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

}