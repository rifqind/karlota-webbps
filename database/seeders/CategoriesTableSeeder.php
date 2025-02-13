<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('categories')->delete();
        
        \DB::table('categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'type' => 'Lapangan Usaha',
                'code' => 'A',
                'name' => 'Pertanian, Kehutanan, dan Perikanan',
            ),
            1 => 
            array (
                'id' => 2,
                'type' => 'Lapangan Usaha',
                'code' => 'B',
                'name' => 'Pertambangan dan Penggalian',
            ),
            2 => 
            array (
                'id' => 3,
                'type' => 'Lapangan Usaha',
                'code' => 'C',
                'name' => 'Industri Pengolahan',
            ),
            3 => 
            array (
                'id' => 4,
                'type' => 'Lapangan Usaha',
                'code' => 'D',
                'name' => 'Pengadaan Listrik dan Gas',
            ),
            4 => 
            array (
                'id' => 5,
                'type' => 'Lapangan Usaha',
                'code' => 'E',
                'name' => 'Pengadaan Air, Pengelolaan Sampah, Limbah, dan Daur Ulang',
            ),
            5 => 
            array (
                'id' => 6,
                'type' => 'Lapangan Usaha',
                'code' => 'F',
                'name' => 'Konstruksi',
            ),
            6 => 
            array (
                'id' => 7,
                'type' => 'Lapangan Usaha',
                'code' => 'G',
                'name' => 'Perdagangan Besar dan Eceran; Reparasi Mobil dan Sepeda Motor',
            ),
            7 => 
            array (
                'id' => 8,
                'type' => 'Lapangan Usaha',
                'code' => 'H',
                'name' => 'Transportasi dan Pergudangan',
            ),
            8 => 
            array (
                'id' => 9,
                'type' => 'Lapangan Usaha',
                'code' => 'I',
                'name' => 'Penyediaan Akomodasi dan Makan Minum',
            ),
            9 => 
            array (
                'id' => 10,
                'type' => 'Lapangan Usaha',
                'code' => 'J',
                'name' => 'Informasi dan Komunikasi',
            ),
            10 => 
            array (
                'id' => 11,
                'type' => 'Lapangan Usaha',
                'code' => 'K',
                'name' => 'Jasa Keuangan dan Asuransi',
            ),
            11 => 
            array (
                'id' => 12,
                'type' => 'Lapangan Usaha',
                'code' => 'L',
                'name' => 'Real Estate',
            ),
            12 => 
            array (
                'id' => 13,
                'type' => 'Lapangan Usaha',
                'code' => 'M,N',
                'name' => 'Jasa Perusahaan',
            ),
            13 => 
            array (
                'id' => 14,
                'type' => 'Lapangan Usaha',
                'code' => 'O',
                'name' => 'Administrasi Pemerintahan, Pertahanan dan Jaminan Sosial Wajib',
            ),
            14 => 
            array (
                'id' => 15,
                'type' => 'Lapangan Usaha',
                'code' => 'P',
                'name' => 'Jasa Pendidikan',
            ),
            15 => 
            array (
                'id' => 16,
                'type' => 'Lapangan Usaha',
                'code' => 'Q',
                'name' => 'Jasa Kesehatan dan Kegiatan Sosial',
            ),
            16 => 
            array (
                'id' => 17,
                'type' => 'Lapangan Usaha',
                'code' => 'R,S,T,U',
                'name' => 'Jasa Lainnya',
            ),
            17 => 
            array (
                'id' => 18,
                'type' => 'Pengeluaran',
                'code' => 'X',
                'name' => 'Pengeluaran',
            ),
        ));
        
        
    }
}