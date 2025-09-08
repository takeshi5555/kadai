<?php

namespace Database\Factories;

use App\Models\Skill; // Skillモデルをuseする
use App\Models\User; // SkillがUserに紐付く場合、Userモデルもuseする
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Skill::class; // ★Skillモデルと紐付ける

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // ここでSkillモデルの各カラムにダミーデータを定義します
        return [
            'user_id' => User::factory(), // ★Userモデルのファクトリと紐付ける
            'title' => $this->faker->sentence(3), // 3単語の文章
            'description' => $this->faker->paragraph(2), // 2段落の文章
            'category' => $this->faker->randomElement(['IT', '語学', 'プログラミング', '健康', 'ビジネス', 'デザイン', 'その他']), // カテゴリを定義済みのものから選ぶ
            // 他に必要なカラムがあればここに追加
        ];
    }
}