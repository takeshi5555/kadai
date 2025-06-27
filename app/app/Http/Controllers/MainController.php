<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skill;
use App\Models\User;
use App\Models\Review;
use App\Models\Matching;

class MainController extends Controller
{
    public function index()
    {
        $newSkills = Skill::with('user')
                            ->orderBy('created_at', 'desc')
                            ->take(6)
                            ->get();

        // カテゴリ名と対応するデフォルト画像ファイル名のマッピングを定義
        // (public/images/categories/ に配置されていると仮定)
        $categoryDefaultImages = [
            'IT' => 'IT.png',
            '語学' => 'language.png',
            'プログラミング' => 'programming.png',
            '健康' => 'yoga.png',
            'ビジネス' => 'business.png',
            'デザイン' => 'design.png', // ★この行を追加してください★
            // もし他のカテゴリがあればここに追加
        ];
        // 汎用的なデフォルトスキル画像（カテゴリに一致するものがなかった場合）
        $defaultSkillImage = 'default.png'; 

        // 各スキルにカテゴリごとのデフォルト画像パスを追加
        $newSkills->map(function ($skill) use ($categoryDefaultImages, $defaultSkillImage) {
            $categoryName = $skill->category; 
            $imageFileName = $categoryDefaultImages[$categoryName] ?? $defaultSkillImage;
            
            // asset() ヘルパーを使って完全なURLを生成
            $skill->default_category_image_path = asset('images/categories/' . $imageFileName);
            
            return $skill;
        });


        // 既存のカテゴリ表示データ（変更なし）
        $fixedCategoriesData = [
            [
                'name' => 'IT',
                'image' => 'images/categories/IT.png'
            ],
            [
                'name' => '語学',
                'image' => 'images/categories/language.png'
            ],
            [
                'name' => 'プログラミング',
                'image' => 'images/categories/programming.png'
            ],
            [
                'name' => '健康',
                'image' => 'images/categories/yoga.png'
            ],
            [
                'name' => 'ビジネス',
                'image' => 'images/categories/business.png'
            ],
            
            [
                 'name' => 'デザイン',
                 'image' => 'images/categories/design.png'
            ],
        ];

        $categoriesToDisplay = collect($fixedCategoriesData)->map(function ($item) {
            return (object) $item;
        });

        // おすすめレビューの取得ロジック（変更なし）
        $featuredReviews = Review::with([
            'reviewer',
            'reviewee',
            'matching.offeringSkill',
            'matching.receivingSkill',
        ])
        ->where('rating', '>=', 4)
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get();

        foreach ($featuredReviews as $review) {
            $displaySkill = null;
            if ($review->matching && $review->reviewee) {
                if ($review->matching->offeringSkill && $review->matching->offeringSkill->user_id === $review->reviewee->id) {
                    $displaySkill = $review->matching->offeringSkill;
                }
                elseif ($review->matching->receivingSkill && $review->matching->receivingSkill->user_id === $review->reviewee->id) {
                    $displaySkill = $review->matching->receivingSkill;
                }
            }
            $review->display_skill = $displaySkill;
        }

        return view('main', compact('newSkills','categoriesToDisplay','featuredReviews'));
    }
}