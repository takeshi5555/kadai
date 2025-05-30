<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporting_user_id',
        'reported_user_id',
        'reportable_type',
        'reportable_id',
        'reason_id', // 大まかな理由
        'sub_reason_id', // より具体的な理由
        'comment',
        'status', // 'unprocessed', 'processed' など
    ];

    // ... (既存のリレーション: reportingUser, reportedUser, reportable) ...

    // 大まかな通報理由
    public function reason()
    {
        return $this->belongsTo(ReportReason::class, 'reason_id');
    }

    // より具体的な通報理由
    public function subReason()
    {
        return $this->belongsTo(ReportReason::class, 'sub_reason_id');
    }
}