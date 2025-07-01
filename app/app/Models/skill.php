<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Skill extends Model
{

    use HasFactory;
    protected $fillable = [
        'user_id', 'title', 'category', 'description','image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function matchings()
    {
        return $this->hasMany(Matching::class); // スキルに対するマッチング
    }


    public function offeredMatchings()
    {
        return $this->hasMany(Matching::class, 'offering_skill_id');
    }

    // このスキルが受講されるマッチング
    public function receivedMatchings()
    {
        return $this->hasMany(Matching::class, 'receiving_skill_id');
    }

     public function reviews()
    {
        // このスキルが提供側または受領側となっているマッチングのIDを全て取得
        $offeringMatchingIds = $this->offeredMatchings()->pluck('id');
        $receivingMatchingIds = $this->receivedMatchings()->pluck('id');

        // 両方のIDを結合し、重複を排除
        $allRelevantMatchingIds = $offeringMatchingIds->merge($receivingMatchingIds)->unique();

        // 取得したマッチングIDに紐づくレビューを取得するクエリを返す
        return Review::whereIn('matching_id', $allRelevantMatchingIds);
    }
        /**
     * スキル画像がない場合のデフォルト画像パスを返すアクセサ
     * カテゴリに基づいて画像を決定する（例: public/images/categories/プログラミング.png）
     * 該当するカテゴリ画像がない場合は、汎用的なdefault.png画像を返す
     *
     * @return string
     */
    public function getDefaultImagePathAttribute()
    {
        // カテゴリ名と対応するデフォルト画像ファイル名のマッピングを定義
        // (public/images/categories/ に配置されていると仮定)
        $categoryDefaultImages = [
            'IT' => 'IT.png',
            '語学' => 'language.png',
            'プログラミング' => 'programming.png',
            '健康' => 'yoga.png',
            'ビジネス' => 'business.png',
            'デザイン' => 'design.png',
            // もし他のカテゴリがあればここに追加
        ];
        // 汎用的なデフォルトスキル画像（カテゴリに一致するものがなかった場合）
        $defaultImageFileName = 'default.png'; 

        $categoryName = $this->category; 
        $imageFileName = $categoryDefaultImages[$categoryName] ?? $defaultImageFileName;
        
        // asset() ヘルパーを使って完全なURLを生成
        return asset('images/categories/' . $imageFileName);
    }

    /**
     * スキルの画像URLを返すアクセサ
     * image_pathがあればそれを使用し、なければデフォルト画像を返す
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return $this->default_image_path; // 上で定義したアクセサを呼び出す
    }
}
