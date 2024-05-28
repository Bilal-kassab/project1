<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Activity::create([
            'id'=>1,
            'name'=>'swimming',
        ]);
        Activity::create([
            'id'=>2,
            'name'=>'eating',
        ]);
        Activity::create([
            'id'=>3,
            'name'=>'skating',
        ]);
    }
}
