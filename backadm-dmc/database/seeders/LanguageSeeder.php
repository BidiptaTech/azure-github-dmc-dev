<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['name' => 'English', 'model' => 'en'],
            ['name' => 'Spanish', 'model' => 'es'],
            ['name' => 'French', 'model' => 'fr'],
            ['name' => 'German', 'model' => 'de'],
            ['name' => 'Chinese', 'model' => 'zh'],
            ['name' => 'Hindi', 'model' => 'hi'],
            ['name' => 'Arabic', 'model' => 'ar'],
            ['name' => 'Russian', 'model' => 'ru'],
            ['name' => 'Portuguese', 'model' => 'pt'],
            ['name' => 'Bengali', 'model' => 'bn'],
            ['name' => 'Japanese', 'model' => 'ja'],
            ['name' => 'Korean', 'model' => 'ko'],
            ['name' => 'Italian', 'model' => 'it'],
            ['name' => 'Dutch', 'model' => 'nl'],
            ['name' => 'Turkish', 'model' => 'tr'],
            ['name' => 'Swedish', 'model' => 'sv'],
            ['name' => 'Greek', 'model' => 'el'],
            ['name' => 'Hebrew', 'model' => 'he'],
            ['name' => 'Thai', 'model' => 'th'],
            ['name' => 'Vietnamese', 'model' => 'vi'],
            ['name' => 'Urdu', 'model' => 'ur'],
            ['name' => 'Persian', 'model' => 'fa'],
        ];

        DB::table('languages')->insert($languages);
    }
}
