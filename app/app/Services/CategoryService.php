<?php

namespace App\Services; // このファイルの名前空間

use Illuminate\Support\Facades\URL; // URL::asset() を使う場合

class CategoryService
{
    // カテゴリごとの画像ファイル名をマッピングするプライベートプロパティ
    private $categoryDefaultImages = [
        'IT' => 'IT.png',
        '語学' => 'language.png',
        'プログラミング' => 'programming.png',
        '健康' => 'yoga.png',
        'ビジネス' => 'business.png',
        'デザイン' => 'design.png',
    ];

    // デフォルトのスキル画像ファイル名
    private $defaultSkillImage = 'default.png';

    /**
     * 指定されたカテゴリ名に対応するデフォルト画像パスを生成して返します。
     * カテゴリが存在しない場合はデフォルトのスキル画像パスを返します。
     */
    public function getCategoryImagePath(string $categoryName): string
    {
        $imageFileName = $this->categoryDefaultImages[$categoryName] ?? $this->defaultSkillImage;
        return 'images/categories/' . $imageFileName; // asset()ヘルパーは呼び出し側で適用するか、URL::asset()を使う
    }

    /**
     * カテゴリデータを表示用に整形し、画像パスをURL化します。
     */
    public function formatCategoriesForDisplay(array $categoriesData): array
    {
        return array_map(function ($item) {
            // ここでURL::asset()を使う場合、Laravelの環境（Service Container）がブートしている必要がある
            // 単体テストでこのメソッドをテストする際は、URL::asset()部分をモック化するか、
            // Featureテストで確認する設計にすると良い
            $item['image'] = URL::asset($item['image']);
            return (object) $item;
        }, $categoriesData);
    }
}