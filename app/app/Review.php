<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'matching_id',
        'reviewer_id',
        'reviewee_id',
        'rating',
        'comment',
    ];

    public function matching()
    {
        return $this->belongsTo(Matching::class);
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
