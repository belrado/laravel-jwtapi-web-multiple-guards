<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'subject_ko' => fake()->name(30),
            'subject_en' => fake()->name('lkasdjfkl'),
            'contents_ko' => '',
            'contents_en' => '',
            'update_admin' => 'u5ink@naver.com',
            'use' => 'y',
            'service' => 'n',
            'service_date' => date('Y-m-d H:i:s', strtotime("+1 days")),
            'hit' => 0,
        ];
    }
}
