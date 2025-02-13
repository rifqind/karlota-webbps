<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RegionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('regions')->delete();
        
        \DB::table('regions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'satker_id' => 1,
                'code' => '7100',
                'name' => 'Provinsi Sulawesi Utara',
            ),
            1 => 
            array (
                'id' => 2,
                'satker_id' => 2,
                'code' => '7101',
                'name' => 'Kab. Bolaang Mongondow',
            ),
            2 => 
            array (
                'id' => 3,
                'satker_id' => 3,
                'code' => '7102',
                'name' => 'Kab. Minahasa',
            ),
            3 => 
            array (
                'id' => 4,
                'satker_id' => 4,
                'code' => '7103',
                'name' => 'Kab. Kepulauan Sangihe',
            ),
            4 => 
            array (
                'id' => 5,
                'satker_id' => 5,
                'code' => '7104',
                'name' => 'Kab. Kepulauan Talaud',
            ),
            5 => 
            array (
                'id' => 6,
                'satker_id' => 6,
                'code' => '7105',
                'name' => 'Kab. Minahasa Selatan',
            ),
            6 => 
            array (
                'id' => 7,
                'satker_id' => 7,
                'code' => '7106',
                'name' => 'Kab. Minahasa Utara',
            ),
            7 => 
            array (
                'id' => 8,
                'satker_id' => 8,
                'code' => '7107',
                'name' => 'Kab. Bolaang Mongondow Utara',
            ),
            8 => 
            array (
                'id' => 9,
                'satker_id' => 9,
                'code' => '7108',
                'name' => 'Kab. Kepulauan Siau Tagulandang Biaro',
            ),
            9 => 
            array (
                'id' => 10,
                'satker_id' => 6,
                'code' => '7109',
                'name' => 'Kab. Minahasa Tenggara',
            ),
            10 => 
            array (
                'id' => 11,
                'satker_id' => 2,
                'code' => '7110',
                'name' => 'Kab. Bolaang Mongondow Selatan',
            ),
            11 => 
            array (
                'id' => 12,
                'satker_id' => 14,
                'code' => '7111',
                'name' => 'Kab. Bolaang Mongondow Timur',
            ),
            12 => 
            array (
                'id' => 13,
                'satker_id' => 10,
                'code' => '7171',
                'name' => 'Kota Manado',
            ),
            13 => 
            array (
                'id' => 14,
                'satker_id' => 11,
                'code' => '7172',
                'name' => 'Kota Bitung',
            ),
            14 => 
            array (
                'id' => 15,
                'satker_id' => 12,
                'code' => '7173',
                'name' => 'Kota Tomohon',
            ),
            15 => 
            array (
                'id' => 16,
                'satker_id' => 13,
                'code' => '7174',
                'name' => 'Kota Kotamobagu',
            ),
        ));
        
        
    }
}