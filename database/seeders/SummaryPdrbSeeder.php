<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\SummaryPdrb;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SummaryPdrbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quarter = [1, 2, 3, 4];
        $region_id = Region::pluck('id');
        foreach ($region_id as $key => $value) {
            # code...
            foreach ($quarter as $key => $qt) {
                # code...
                $normalized = [];
                $default = [
                    'category_id' => null,
                    'sector_id' => null,
                    'subsector_id' => null,
                    'region_id' => null,
                    'quarter' => null,
                    'adhb' => null,
                    'adhk' => null,
                    'dist' => null,
                    'qtoq' => null,
                    'yony' => null,
                    'ctoc' => null,
                    'idx' => null,
                    'iqtoq' => null,
                    'iyony' => null,
                ];
                $data = [
                    ['category_id' => 1, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 1, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 1, 'subsector_id' => 1, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 1, 'subsector_id' => 2, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 1, 'subsector_id' => 3, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 1, 'subsector_id' => 4, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 1, 'subsector_id' => 5, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 1, 'subsector_id' => 6, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 1, 'subsector_id' => 7, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 2, 'subsector_id' => 8, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 1, 'sector_id' => 3, 'subsector_id' => 9, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 2, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 2, 'sector_id' => 4, 'subsector_id' => 10, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 2, 'sector_id' => 5, 'subsector_id' => 11, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 2, 'sector_id' => 6, 'subsector_id' => 12, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 2, 'sector_id' => 7, 'subsector_id' => 13, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 8, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 8, 'subsector_id' => 14, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 8, 'subsector_id' => 15, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 9, 'subsector_id' => 16, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 10, 'subsector_id' => 17, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 11, 'subsector_id' => 18, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 12, 'subsector_id' => 19, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 13, 'subsector_id' => 20, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 14, 'subsector_id' => 21, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 15, 'subsector_id' => 22, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 16, 'subsector_id' => 23, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 17, 'subsector_id' => 24, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 18, 'subsector_id' => 25, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 19, 'subsector_id' => 26, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 20, 'subsector_id' => 27, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 21, 'subsector_id' => 28, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 22, 'subsector_id' => 29, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 3, 'sector_id' => 23, 'subsector_id' => 30, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 4, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 4, 'sector_id' => 24, 'subsector_id' => 31, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 4, 'sector_id' => 25, 'subsector_id' => 32, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 5, 'sector_id' => 26, 'subsector_id' => 33, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 6, 'sector_id' => 27, 'subsector_id' => 34, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 7, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 7, 'sector_id' => 28, 'subsector_id' => 35, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 7, 'sector_id' => 29, 'subsector_id' => 36, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 8, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 8, 'sector_id' => 30, 'subsector_id' => 37, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 8, 'sector_id' => 31, 'subsector_id' => 38, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 8, 'sector_id' => 32, 'subsector_id' => 39, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 8, 'sector_id' => 33, 'subsector_id' => 40, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 8, 'sector_id' => 34, 'subsector_id' => 41, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 8, 'sector_id' => 35, 'subsector_id' => 42, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 9, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 9, 'sector_id' => 36, 'subsector_id' => 43, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 9, 'sector_id' => 37, 'subsector_id' => 44, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 10, 'sector_id' => 38, 'subsector_id' => 45, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 11, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 11, 'sector_id' => 39, 'subsector_id' => 46, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 11, 'sector_id' => 40, 'subsector_id' => 47, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 11, 'sector_id' => 41, 'subsector_id' => 48, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 11, 'sector_id' => 42, 'subsector_id' => 49, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 12, 'sector_id' => 43, 'subsector_id' => 50, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 13, 'sector_id' => 44, 'subsector_id' => 51, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 14, 'sector_id' => 45, 'subsector_id' => 52, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 15, 'sector_id' => 46, 'subsector_id' => 53, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 16, 'sector_id' => 47, 'subsector_id' => 54, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 17, 'sector_id' => 48, 'subsector_id' => 55, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 98, 'sector_id' => 98, 'subsector_id' => 98, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 49, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 49, 'subsector_id' => 56, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 49, 'subsector_id' => 57, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 49, 'subsector_id' => 58, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 49, 'subsector_id' => 59, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 49, 'subsector_id' => 60, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 49, 'subsector_id' => 61, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 49, 'subsector_id' => 62, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 50, 'subsector_id' => 63, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 51, 'subsector_id' => 64, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 52, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 52, 'subsector_id' => 65, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 52, 'subsector_id' => 66, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 53, 'subsector_id' => 67, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 54, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 54, 'subsector_id' => 68, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 18, 'sector_id' => 54, 'subsector_id' => 69, 'region_id' => $value, 'quarter' => $qt],
                    ['category_id' => 99, 'sector_id' => 99, 'subsector_id' => 99, 'region_id' => $value, 'quarter' => $qt],
                ];
                foreach($data as $item) {
                    $normalized[] = array_merge($default, $item);
                }
                SummaryPdrb::insert($normalized);
            }
        }
    }
}
