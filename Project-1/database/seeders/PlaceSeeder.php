<?php

namespace Database\Seeders;

use App\Helpers\MakePlaceHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $text="This iconic iron lattice tower in Paris is not only a famous landmark but also an engineering marvel. Climb to the top for breathtaking views of the city and beyond";
        MakePlaceHelper::makePlace('Eiffel Tower',20,$text,4,[1,2],'36.11272365940481','-115.17260869014497');

        $text="Located in Paris, the Louvre is one of the world's most prestigious museums. It houses masterpieces like the Mona Lisa and the Venus de Milo";
        MakePlaceHelper::makePlace('Musée du Louvre',20,$text,4,[1,2],'48.86147245','2.3368747442628885');

        $text="The Umayyad Mosque, also known as the Great Mosque of Damascus, is one of the most significant Islamic architectural masterpieces in the world. Located in the heart of Damascus, Syria, it is a testament to the grandeur and architectural prowess of the Umayyad Caliphate, which ruled from the 7th to the 8th century.";
        MakePlaceHelper::makePlace('Umayyad Mosque',5,$text,1,[1,2],'33.51158435','36.306652253638944');
    }
}
