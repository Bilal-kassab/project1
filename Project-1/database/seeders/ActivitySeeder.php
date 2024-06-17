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
            'name'=>'Swimming',
        ]);
        Activity::create([
            'name'=>'Eating',
        ]);
        Activity::create([
            'name'=>'Skating',
        ]);
        Activity::create([
            'name'=>'Fishing',
        ]);
        Activity::create([
            'name'=>'Camping',
        ]);
        Activity::create([
            'name'=>'Cycling',
        ]);
        Activity::create([
            'name'=>'Safari',
        ]);

    }
}
