<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NepalLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            'Koshi Province' => [
                'Biratnagar', 'Dharan', 'Itahari', 'Damak', 'Birtamod',
            ],
            'Madhesh Province' => [
                'Janakpur', 'Birgunj', 'Kalaiya', 'Jaleshwar', 'Malangwa',
            ],
            'Bagmati Province' => [
                'Kathmandu', 'Lalitpur', 'Bhaktapur', 'Hetauda', 'Chitwan',
            ],
            'Gandaki Province' => [
                'Pokhara', 'Baglung', 'Gorkha', 'Damauli', 'Beni',
            ],
            'Lumbini Province' => [
                'Butwal', 'Bhairahawa', 'Nepalgunj', 'Tansen', 'Kapilvastu',
            ],
            'Karnali Province' => [
                'Birendranagar', 'Jumla', 'Dailekh', 'Kalikot', 'Mugu',
            ],
            'Sudurpashchim Province' => [
                'Dhangadhi', 'Mahendranagar', 'Dadeldhura', 'Dipayal', 'Bajhang',
            ],
        ];

        foreach ($locations as $provinceName => $cities) {
            $province = Province::query()->updateOrCreate(
                ['slug' => Str::slug($provinceName)],
                ['name' => $provinceName]
            );

            foreach ($cities as $cityName) {
                $isServiceable = $provinceName === 'Bagmati Province'
                    && in_array($cityName, ['Kathmandu', 'Lalitpur', 'Bhaktapur'], true);

                City::query()->updateOrCreate(
                    [
                        'province_id' => $province->id,
                        'slug' => Str::slug($cityName),
                    ],
                    [
                        'name' => $cityName,
                        'is_active' => true,
                        'is_serviceable' => $isServiceable,
                    ]
                );
            }
        }
    }
}
