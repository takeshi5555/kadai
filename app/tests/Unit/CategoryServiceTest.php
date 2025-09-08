<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\CategoryService;

class CategoryServiceTest extends TestCase
{
    /** @test */
    public function it_returns_correct_category_image_path()
    {
        $service = new CategoryService();

        // 存在するカテゴリの場合
        $this->assertEquals('images/categories/IT.png', $service->getCategoryImagePath('IT'));
        $this->assertEquals('images/categories/design.png', $service->getCategoryImagePath('デザイン'));

        // 存在しないカテゴリの場合
        $this->assertEquals('images/categories/default.png', $service->getCategoryImagePath('存在しないカテゴリ'));
    }


    
}