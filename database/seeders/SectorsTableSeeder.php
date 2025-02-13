<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SectorsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sectors')->delete();
        
        \DB::table('sectors')->insert(array (
            0 => 
            array (
                'id' => 1,
                'category_id' => 1,
                'code' => '1',
                'name' => 'Pertanian, Peternakan, Perburuan dan Jasa',
            ),
            1 => 
            array (
                'id' => 2,
                'category_id' => 1,
                'code' => '2',
                'name' => 'Kehutanan dan Penebangan Kayu',
            ),
            2 => 
            array (
                'id' => 3,
                'category_id' => 1,
                'code' => '3',
                'name' => 'Perikanan',
            ),
            3 => 
            array (
                'id' => 4,
                'category_id' => 2,
                'code' => '1',
                'name' => 'Pertambangan Minyak, Gas dan Panas Bumi',
            ),
            4 => 
            array (
                'id' => 5,
                'category_id' => 2,
                'code' => '2',
                'name' => 'Pertambangan Batubara dan Lignit',
            ),
            5 => 
            array (
                'id' => 6,
                'category_id' => 2,
                'code' => '3',
                'name' => 'Pertambangan Bijih Logam',
            ),
            6 => 
            array (
                'id' => 7,
                'category_id' => 2,
                'code' => '4',
                'name' => 'Pertambangan dan Penggalian Lainnya',
            ),
            7 => 
            array (
                'id' => 8,
                'category_id' => 3,
                'code' => '1',
                'name' => 'Industri Batubara dan Pengilangan Migas',
            ),
            8 => 
            array (
                'id' => 9,
                'category_id' => 3,
                'code' => '2',
                'name' => 'Industri Makanan dan Minuman',
            ),
            9 => 
            array (
                'id' => 10,
                'category_id' => 3,
                'code' => '3',
                'name' => 'Pengolahan Tembakau',
            ),
            10 => 
            array (
                'id' => 11,
                'category_id' => 3,
                'code' => '4',
                'name' => 'Industri Tekstil dan Pakaian Jadi',
            ),
            11 => 
            array (
                'id' => 12,
                'category_id' => 3,
                'code' => '5',
                'name' => 'Industri Kulit, Barang dari Kulit dan Alas Kaki',
            ),
            12 => 
            array (
                'id' => 13,
                'category_id' => 3,
                'code' => '6',
                'name' => 'Industri Kayu, Barang dari Kayu dan Gabus dan Barang Anyaman dari Bambu, Rotan dan Sejenisnya',
            ),
            13 => 
            array (
                'id' => 14,
                'category_id' => 3,
                'code' => '7',
                'name' => 'Industri Kertas dan Barang dari Kertas, Percetakan dan Reproduksi Media Rekaman',
            ),
            14 => 
            array (
                'id' => 15,
                'category_id' => 3,
                'code' => '8',
                'name' => 'Industri Kimia, Farmasi dan Obat Tradisional',
            ),
            15 => 
            array (
                'id' => 16,
                'category_id' => 3,
                'code' => '9',
                'name' => 'Industri Karet, Barang dari Karet dan Plastik',
            ),
            16 => 
            array (
                'id' => 17,
                'category_id' => 3,
                'code' => '10',
                'name' => 'Industri Barang Galian bukan Logam',
            ),
            17 => 
            array (
                'id' => 18,
                'category_id' => 3,
                'code' => '11',
                'name' => 'Industri Logam Dasar',
            ),
            18 => 
            array (
                'id' => 19,
                'category_id' => 3,
                'code' => '12',
                'name' => 'Industri Barang dari Logam, Komputer, Barang Elektronik, Optik dan Peralatan Listrik',
            ),
            19 => 
            array (
                'id' => 20,
                'category_id' => 3,
                'code' => '13',
                'name' => 'Industri Mesin dan Perlengkapan YTDL',
            ),
            20 => 
            array (
                'id' => 21,
                'category_id' => 3,
                'code' => '14',
                'name' => 'Industri Alat Angkutan',
            ),
            21 => 
            array (
                'id' => 22,
                'category_id' => 3,
                'code' => '15',
                'name' => 'Industri Furnitur',
            ),
            22 => 
            array (
                'id' => 23,
                'category_id' => 3,
                'code' => '16',
                'name' => 'Industri pengolahan lainnya, jasa reparasi dan pemasangan mesin dan peralatan',
            ),
            23 => 
            array (
                'id' => 24,
                'category_id' => 4,
                'code' => '1',
                'name' => 'Ketenagalistrikan',
            ),
            24 => 
            array (
                'id' => 25,
                'category_id' => 4,
                'code' => '2',
                'name' => ' Pengadaan Gas dan Produksi Es',
            ),
            25 => 
            array (
                'id' => 26,
                'category_id' => 5,
                'code' => NULL,
                'name' => 'Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang',
            ),
            26 => 
            array (
                'id' => 27,
                'category_id' => 6,
                'code' => NULL,
                'name' => 'Konstruksi',
            ),
            27 => 
            array (
                'id' => 28,
                'category_id' => 7,
                'code' => '1',
                'name' => 'Perdagangan Mobil, Sepeda Motor dan Reparasinya',
            ),
            28 => 
            array (
                'id' => 29,
                'category_id' => 7,
                'code' => '2',
                'name' => ' Perdagangan Besar dan Eceran, Bukan Mobil dan Sepeda Motor',
            ),
            29 => 
            array (
                'id' => 30,
                'category_id' => 8,
                'code' => '1',
                'name' => 'Angkutan Rel',
            ),
            30 => 
            array (
                'id' => 31,
                'category_id' => 8,
                'code' => '2',
                'name' => 'Angkutan Darat',
            ),
            31 => 
            array (
                'id' => 32,
                'category_id' => 8,
                'code' => '3',
                'name' => 'Angkutan Laut',
            ),
            32 => 
            array (
                'id' => 33,
                'category_id' => 8,
                'code' => '4',
                'name' => 'Angkutan Sungai Danau dan Penyeberangan',
            ),
            33 => 
            array (
                'id' => 34,
                'category_id' => 8,
                'code' => '5',
                'name' => 'Angkutan Udara',
            ),
            34 => 
            array (
                'id' => 35,
                'category_id' => 8,
                'code' => '6',
                'name' => 'Pergudangan dan Jasa Penunjang Angkutan, Pos dan Kurir',
            ),
            35 => 
            array (
                'id' => 36,
                'category_id' => 9,
                'code' => '1',
                'name' => 'Penyediaan Akomodasi',
            ),
            36 => 
            array (
                'id' => 37,
                'category_id' => 9,
                'code' => '2',
                'name' => 'Penyediaan Makan Minum',
            ),
            37 => 
            array (
                'id' => 38,
                'category_id' => 10,
                'code' => NULL,
                'name' => 'Informasi dan Komunikasi',
            ),
            38 => 
            array (
                'id' => 39,
                'category_id' => 11,
                'code' => '1',
                'name' => 'Jasa Perantara Keuangan',
            ),
            39 => 
            array (
                'id' => 40,
                'category_id' => 11,
                'code' => '2',
                'name' => 'Asuransi dan Dana Pensiun',
            ),
            40 => 
            array (
                'id' => 41,
                'category_id' => 11,
                'code' => '3',
                'name' => 'Jasa Keuangan Lainnya',
            ),
            41 => 
            array (
                'id' => 42,
                'category_id' => 11,
                'code' => '4',
                'name' => 'Jasa Penunjang Keuangan',
            ),
            42 => 
            array (
                'id' => 43,
                'category_id' => 12,
                'code' => NULL,
                'name' => 'Real Estate',
            ),
            43 => 
            array (
                'id' => 44,
                'category_id' => 13,
                'code' => NULL,
                'name' => 'Jasa Perusahaan',
            ),
            44 => 
            array (
                'id' => 45,
                'category_id' => 14,
                'code' => NULL,
                'name' => 'Administrasi Pemerintahan, Pertahanan dan Jaminan Sosial Wajib',
            ),
            45 => 
            array (
                'id' => 46,
                'category_id' => 15,
                'code' => NULL,
                'name' => 'Jasa Pendidikan',
            ),
            46 => 
            array (
                'id' => 47,
                'category_id' => 16,
                'code' => NULL,
                'name' => 'Jasa Kesehatan dan Kegiatan Sosial',
            ),
            47 => 
            array (
                'id' => 48,
                'category_id' => 17,
                'code' => NULL,
                'name' => 'Jasa Lainnya',
            ),
            48 => 
            array (
                'id' => 49,
                'category_id' => 18,
                'code' => '1',
                'name' => 'Pengeluaran Konsumsi Rumah Tangga',
            ),
            49 => 
            array (
                'id' => 50,
                'category_id' => 18,
                'code' => '2',
                'name' => 'Pengeluaran Konsumsi LNPRT',
            ),
            50 => 
            array (
                'id' => 51,
                'category_id' => 18,
                'code' => '3',
                'name' => 'Pengeluaran Konsumsi Pemerintah',
            ),
            51 => 
            array (
                'id' => 52,
                'category_id' => 18,
                'code' => '4',
                'name' => 'Pembentukan Modal Tetap Bruto',
            ),
            52 => 
            array (
                'id' => 53,
                'category_id' => 18,
                'code' => '5',
                'name' => 'Perubahan Inventori',
            ),
            53 => 
            array (
                'id' => 54,
                'category_id' => 18,
                'code' => '6',
                'name' => 'Net Ekspor',
            ),
        ));
        
        
    }
}