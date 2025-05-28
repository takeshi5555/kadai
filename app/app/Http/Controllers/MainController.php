<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Skill; 
use App\User;
use App\Review;
use App\Matching;

class MainController extends Controller
{
    public function index()
    {
        // ここにメインページに表示するデータを取得するロジックを記述します
        // 例えば、全てのスキルを取得して表示したい場合:
        // use App\Models\Skill; // 先頭にこれを追加 (まだモデルがなければ後で追加)
        // $skills = Skill::orderBy('created_at', 'desc')->get();
        // return view('main', compact('skills'));

        // まずはシンプルなビューを返す
        $newSkills = Skill::with('user') // ★ここが重要
                           ->orderBy('created_at', 'desc')
                           ->take(6)
                           ->get();

                           $categories = Skill::distinct('category')->pluck('category');

        $featuredReviews = Review::with(['reviewer', 'reviewee', 'matching.offeringSkill']) // ★ここを修正
                                 ->where('rating', '>=', 4)
                                 ->orderBy('created_at', 'desc')
                                 ->take(3)
                                 ->get();

        return view('main', compact('newSkills', 'categories','featuredReviews'));
    }
}