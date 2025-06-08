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

        $featuredReviews = Review::with(['reviewer', 'reviewee', 'matching.offeringSkill']) 
                                 ->where('rating', '>=', 4)
                                 ->orderBy('created_at', 'desc')
                                 ->take(3)
                                 ->get();

        return view('main', compact('newSkills','categoriesToDisplay','featuredReviews'));
    }
}