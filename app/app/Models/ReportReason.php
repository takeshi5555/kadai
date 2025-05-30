<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'reason_text',
    ];

    /**
     * 親の通報理由を取得
     */
    public function parent()
    {
        return $this->belongsTo(ReportReason::class, 'parent_id');
    }

    /**
     * 子の通報理由（より具体的な選択肢）を取得
     */
    public function children()
    {
        return $this->hasMany(ReportReason::class, 'parent_id');
    }

    /**
     * 親を持たない（第一階層の）理由のみを取得するスコープ
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}