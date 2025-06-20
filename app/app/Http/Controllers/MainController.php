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
        // まずはシンプルなビューを返す
        $newSkills = Skill::with('user')
                           ->orderBy('created_at', 'desc')
                           ->take(6)
                           ->get();

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
        ];

        $categoriesToDisplay = collect($fixedCategoriesData)->map(function ($item) {
            return (object) $item;
        });

        // 必要なリレーションをまとめてロードします。
        // matching.offeringSkill と matching.receivingSkill を両方ロードするように変更。
        // reviewee もロードして、後でreviewee_idとの比較に使用します。
        $featuredReviews = Review::with([
            'reviewer',
            'reviewee',
            'matching.offeringSkill',    // offering_skill_id に紐づくスキル
            'matching.receivingSkill',   // receiving_skill_id に紐づくスキル
        ])
        ->where('rating', '>=', 4)
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get();

        // 各レビューに対して、表示すべき「提供スキル」を特定し、カスタムプロパティとして追加
        foreach ($featuredReviews as $review) {
            $displaySkill = null;

            // マッチングが存在し、かつreviewee（レビューされたユーザー）が特定できる場合
            if ($review->matching && $review->reviewee) {
                // reviewee が offering_skill を提供したユーザーと一致するか
                // (offeringSkillはMatchingのoffering_skill_idに紐づくSkill)
                if ($review->matching->offeringSkill && $review->matching->offeringSkill->user_id === $review->reviewee->id) {
                    $displaySkill = $review->matching->offeringSkill;
                }
                // reviewee が receiving_skill を提供したユーザーと一致するか
                // (receivingSkillはMatchingのreceiving_skill_idに紐づくSkill)
                // ※もし receiving_skill が「利用されたスキル」を指すのであれば、ここは含めないか、
                //   別のロジックを検討する必要があります。
                //   ここでは、両方のスキルが「提供されうるもの」としてユーザーに紐づいていると仮定します。
                elseif ($review->matching->receivingSkill && $review->matching->receivingSkill->user_id === $review->reviewee->id) {
                    $displaySkill = $review->matching->receivingSkill;
                }
            }
            // どのスキルを特定できなかった場合でもnullのまま
            $review->display_skill = $displaySkill;
        }

        return view('main', compact('newSkills','categoriesToDisplay','featuredReviews'));
    }
}