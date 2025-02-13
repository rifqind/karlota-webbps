<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SubsectorsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('subsectors')->delete();
        
        \DB::table('subsectors')->insert(array (
            0 => 
            array (
                'id' => 1,
                'type' => 'Lapangan Usaha',
                'sector_id' => 1,
                'code' => 'a',
                'name' => 'Tanaman Pangan',
            ),
            1 => 
            array (
                'id' => 2,
                'type' => 'Lapangan Usaha',
                'sector_id' => 1,
                'code' => 'b',
                'name' => 'Tanaman Hortikultura Semusim',
            ),
            2 => 
            array (
                'id' => 3,
                'type' => 'Lapangan Usaha',
                'sector_id' => 1,
                'code' => 'c',
                'name' => 'Perkebunan Semusim',
            ),
            3 => 
            array (
                'id' => 4,
                'type' => 'Lapangan Usaha',
                'sector_id' => 1,
                'code' => 'd',
                'name' => 'Tanaman Hortikultura Tahunan dan Lainnya',
            ),
            4 => 
            array (
                'id' => 5,
                'type' => 'Lapangan Usaha',
                'sector_id' => 1,
                'code' => 'e',
                'name' => 'Perkebunan Tahunan',
            ),
            5 => 
            array (
                'id' => 6,
                'type' => 'Lapangan Usaha',
                'sector_id' => 1,
                'code' => 'f',
                'name' => 'Peternakan',
            ),
            6 => 
            array (
                'id' => 7,
                'type' => 'Lapangan Usaha',
                'sector_id' => 1,
                'code' => 'g',
                'name' => 'Jasa Pertanian dan Perburuan',
            ),
            7 => 
            array (
                'id' => 8,
                'type' => 'Lapangan Usaha',
                'sector_id' => 2,
                'code' => NULL,
                'name' => 'Kehutanan dan Penebangan Kayu',
            ),
            8 => 
            array (
                'id' => 9,
                'type' => 'Lapangan Usaha',
                'sector_id' => 3,
                'code' => NULL,
                'name' => 'Perikanan',
            ),
            9 => 
            array (
                'id' => 10,
                'type' => 'Lapangan Usaha',
                'sector_id' => 4,
                'code' => NULL,
                'name' => 'Pertambangan Minyak, Gas dan Panas Bumi',
            ),
            10 => 
            array (
                'id' => 11,
                'type' => 'Lapangan Usaha',
                'sector_id' => 5,
                'code' => NULL,
                'name' => 'Pertambangan Batubara dan Lignit',
            ),
            11 => 
            array (
                'id' => 12,
                'type' => 'Lapangan Usaha',
                'sector_id' => 6,
                'code' => NULL,
                'name' => 'Pertambangan Bijih Logam',
            ),
            12 => 
            array (
                'id' => 13,
                'type' => 'Lapangan Usaha',
                'sector_id' => 7,
                'code' => NULL,
                'name' => 'Pertambangan dan Penggalian Lainnya',
            ),
            13 => 
            array (
                'id' => 14,
                'type' => 'Lapangan Usaha',
                'sector_id' => 8,
                'code' => 'a',
                'name' => 'Industri Batubara',
            ),
            14 => 
            array (
                'id' => 15,
                'type' => 'Lapangan Usaha',
                'sector_id' => 8,
                'code' => 'b',
                'name' => 'Pengilangan Migas',
            ),
            15 => 
            array (
                'id' => 16,
                'type' => 'Lapangan Usaha',
                'sector_id' => 9,
                'code' => NULL,
                'name' => 'Industri Makanan dan Minuman',
            ),
            16 => 
            array (
                'id' => 17,
                'type' => 'Lapangan Usaha',
                'sector_id' => 10,
                'code' => NULL,
                'name' => 'Pengolahan Tembakau',
            ),
            17 => 
            array (
                'id' => 18,
                'type' => 'Lapangan Usaha',
                'sector_id' => 11,
                'code' => NULL,
                'name' => 'Industri Tekstil dan Pakaian Jadi',
            ),
            18 => 
            array (
                'id' => 19,
                'type' => 'Lapangan Usaha',
                'sector_id' => 12,
                'code' => NULL,
                'name' => 'Industri Kulit, Barang dari Kulit dan Alas Kaki',
            ),
            19 => 
            array (
                'id' => 20,
                'type' => 'Lapangan Usaha',
                'sector_id' => 13,
                'code' => NULL,
                'name' => 'Industri Kayu, Barang dari Kayu dan Gabus dan Barang Anyaman dari Bambu, Rotan dan Sejenisnya',
            ),
            20 => 
            array (
                'id' => 21,
                'type' => 'Lapangan Usaha',
                'sector_id' => 14,
                'code' => NULL,
                'name' => 'Industri Kertas dan Barang dari Kertas, Percetakan dan Reproduksi Media Rekaman',
            ),
            21 => 
            array (
                'id' => 22,
                'type' => 'Lapangan Usaha',
                'sector_id' => 15,
                'code' => NULL,
                'name' => 'Industri Kimia, Farmasi dan Obat Tradisional',
            ),
            22 => 
            array (
                'id' => 23,
                'type' => 'Lapangan Usaha',
                'sector_id' => 16,
                'code' => NULL,
                'name' => 'Industri Karet, Barang dari Karet dan Plastik',
            ),
            23 => 
            array (
                'id' => 24,
                'type' => 'Lapangan Usaha',
                'sector_id' => 17,
                'code' => NULL,
                'name' => 'Industri Barang Galian bukan Logam',
            ),
            24 => 
            array (
                'id' => 25,
                'type' => 'Lapangan Usaha',
                'sector_id' => 18,
                'code' => NULL,
                'name' => 'Industri Logam Dasar',
            ),
            25 => 
            array (
                'id' => 26,
                'type' => 'Lapangan Usaha',
                'sector_id' => 19,
                'code' => NULL,
                'name' => 'Industri Barang dari Logam, Komputer, Barang Elektronik, Optik dan Peralatan Listrik',
            ),
            26 => 
            array (
                'id' => 27,
                'type' => 'Lapangan Usaha',
                'sector_id' => 20,
                'code' => NULL,
                'name' => 'Industri Mesin dan Perlengkapan YTDL',
            ),
            27 => 
            array (
                'id' => 28,
                'type' => 'Lapangan Usaha',
                'sector_id' => 21,
                'code' => NULL,
                'name' => 'Industri Alat Angkutan',
            ),
            28 => 
            array (
                'id' => 29,
                'type' => 'Lapangan Usaha',
                'sector_id' => 22,
                'code' => NULL,
                'name' => 'Industri Furnitur',
            ),
            29 => 
            array (
                'id' => 30,
                'type' => 'Lapangan Usaha',
                'sector_id' => 23,
                'code' => NULL,
                'name' => 'Industri pengolahan lainnya, jasa reparasi dan pemasangan mesin dan peralatan',
            ),
            30 => 
            array (
                'id' => 31,
                'type' => 'Lapangan Usaha',
                'sector_id' => 24,
                'code' => NULL,
                'name' => 'Ketenagalistrikan',
            ),
            31 => 
            array (
                'id' => 32,
                'type' => 'Lapangan Usaha',
                'sector_id' => 25,
                'code' => NULL,
                'name' => ' Pengadaan Gas dan Produksi Es',
            ),
            32 => 
            array (
                'id' => 33,
                'type' => 'Lapangan Usaha',
                'sector_id' => 26,
                'code' => NULL,
                'name' => 'Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang',
            ),
            33 => 
            array (
                'id' => 34,
                'type' => 'Lapangan Usaha',
                'sector_id' => 27,
                'code' => NULL,
                'name' => 'Konstruksi',
            ),
            34 => 
            array (
                'id' => 35,
                'type' => 'Lapangan Usaha',
                'sector_id' => 28,
                'code' => NULL,
                'name' => 'Perdagangan Mobil, Sepeda Motor dan Reparasinya',
            ),
            35 => 
            array (
                'id' => 36,
                'type' => 'Lapangan Usaha',
                'sector_id' => 29,
                'code' => NULL,
                'name' => ' Perdagangan Besar dan Eceran, Bukan Mobil dan Sepeda Motor',
            ),
            36 => 
            array (
                'id' => 37,
                'type' => 'Lapangan Usaha',
                'sector_id' => 30,
                'code' => NULL,
                'name' => 'Angkutan Rel',
            ),
            37 => 
            array (
                'id' => 38,
                'type' => 'Lapangan Usaha',
                'sector_id' => 31,
                'code' => NULL,
                'name' => 'Angkutan Darat',
            ),
            38 => 
            array (
                'id' => 39,
                'type' => 'Lapangan Usaha',
                'sector_id' => 32,
                'code' => NULL,
                'name' => 'Angkutan Laut',
            ),
            39 => 
            array (
                'id' => 40,
                'type' => 'Lapangan Usaha',
                'sector_id' => 33,
                'code' => NULL,
                'name' => 'Angkutan Sungai Danau dan Penyeberangan',
            ),
            40 => 
            array (
                'id' => 41,
                'type' => 'Lapangan Usaha',
                'sector_id' => 34,
                'code' => NULL,
                'name' => 'Angkutan Udara',
            ),
            41 => 
            array (
                'id' => 42,
                'type' => 'Lapangan Usaha',
                'sector_id' => 35,
                'code' => NULL,
                'name' => 'Pergudangan dan Jasa Penunjang Angkutan, Pos dan Kurir',
            ),
            42 => 
            array (
                'id' => 43,
                'type' => 'Lapangan Usaha',
                'sector_id' => 36,
                'code' => NULL,
                'name' => 'Penyediaan Akomodasi',
            ),
            43 => 
            array (
                'id' => 44,
                'type' => 'Lapangan Usaha',
                'sector_id' => 37,
                'code' => NULL,
                'name' => 'Penyediaan Makan Minum',
            ),
            44 => 
            array (
                'id' => 45,
                'type' => 'Lapangan Usaha',
                'sector_id' => 38,
                'code' => NULL,
                'name' => 'Informasi dan Komunikasi',
            ),
            45 => 
            array (
                'id' => 46,
                'type' => 'Lapangan Usaha',
                'sector_id' => 39,
                'code' => NULL,
                'name' => 'Jasa Perantara Keuangan',
            ),
            46 => 
            array (
                'id' => 47,
                'type' => 'Lapangan Usaha',
                'sector_id' => 40,
                'code' => NULL,
                'name' => 'Asuransi dan Dana Pensiun',
            ),
            47 => 
            array (
                'id' => 48,
                'type' => 'Lapangan Usaha',
                'sector_id' => 41,
                'code' => NULL,
                'name' => 'Jasa Keuangan Lainnya',
            ),
            48 => 
            array (
                'id' => 49,
                'type' => 'Lapangan Usaha',
                'sector_id' => 42,
                'code' => NULL,
                'name' => 'Jasa Penunjang Keuangan',
            ),
            49 => 
            array (
                'id' => 50,
                'type' => 'Lapangan Usaha',
                'sector_id' => 43,
                'code' => NULL,
                'name' => 'Real Estate',
            ),
            50 => 
            array (
                'id' => 51,
                'type' => 'Lapangan Usaha',
                'sector_id' => 44,
                'code' => NULL,
                'name' => 'Jasa Perusahaan',
            ),
            51 => 
            array (
                'id' => 52,
                'type' => 'Lapangan Usaha',
                'sector_id' => 45,
                'code' => NULL,
                'name' => 'Administrasi Pemerintahan, Pertahanan dan Jaminan Sosial Wajib',
            ),
            52 => 
            array (
                'id' => 53,
                'type' => 'Lapangan Usaha',
                'sector_id' => 46,
                'code' => NULL,
                'name' => 'Jasa Pendidikan',
            ),
            53 => 
            array (
                'id' => 54,
                'type' => 'Lapangan Usaha',
                'sector_id' => 47,
                'code' => NULL,
                'name' => 'Jasa Kesehatan dan Kegiatan Sosial',
            ),
            54 => 
            array (
                'id' => 55,
                'type' => 'Lapangan Usaha',
                'sector_id' => 48,
                'code' => NULL,
                'name' => 'Jasa Lainnya',
            ),
            55 => 
            array (
                'id' => 56,
                'type' => 'Pengeluaran',
                'sector_id' => 49,
                'code' => 'a',
                'name' => 'Makanan, Minuman dan Rokok',
            ),
            56 => 
            array (
                'id' => 57,
                'type' => 'Pengeluaran',
                'sector_id' => 49,
                'code' => 'b',
                'name' => 'Pakaian dan Alas Kaki',
            ),
            57 => 
            array (
                'id' => 58,
                'type' => 'Pengeluaran',
                'sector_id' => 49,
                'code' => 'c',
                'name' => 'Perumahan, Perkakas, Perlengkapan dan Penyelenggaraan Rumah Tangga',
            ),
            58 => 
            array (
                'id' => 59,
                'type' => 'Pengeluaran',
                'sector_id' => 49,
                'code' => 'd',
                'name' => 'Kesehatan dan Pendidikan',
            ),
            59 => 
            array (
                'id' => 60,
                'type' => 'Pengeluaran',
                'sector_id' => 49,
                'code' => 'e',
                'name' => 'Transportasi, Komunikasi, Rekreasi, dan Budaya',
            ),
            60 => 
            array (
                'id' => 61,
                'type' => 'Pengeluaran',
                'sector_id' => 49,
                'code' => 'f',
                'name' => 'Hotel dan Restoran',
            ),
            61 => 
            array (
                'id' => 62,
                'type' => 'Pengeluaran',
                'sector_id' => 49,
                'code' => 'g',
                'name' => 'Lainnya',
            ),
            62 => 
            array (
                'id' => 63,
                'type' => 'Pengeluaran',
                'sector_id' => 50,
                'code' => NULL,
                'name' => 'Pengeluaran Konsumsi LNPRT',
            ),
            63 => 
            array (
                'id' => 64,
                'type' => 'Pengeluaran',
                'sector_id' => 51,
                'code' => NULL,
                'name' => 'Pengeluaran Konsumsi Pemerintah',
            ),
            64 => 
            array (
                'id' => 65,
                'type' => 'Pengeluaran',
                'sector_id' => 52,
                'code' => 'a',
                'name' => 'Bangunan',
            ),
            65 => 
            array (
                'id' => 66,
                'type' => 'Pengeluaran',
                'sector_id' => 52,
                'code' => 'b',
                'name' => 'Non Bangunan',
            ),
            66 => 
            array (
                'id' => 67,
                'type' => 'Pengeluaran',
                'sector_id' => 53,
                'code' => NULL,
                'name' => 'Perubahan Inventori',
            ),
            67 => 
            array (
                'id' => 68,
                'type' => 'Pengeluaran',
                'sector_id' => 54,
                'code' => 'a',
            'name' => 'Ekspor Barang dan Jasa (LN + AP)',
            ),
            68 => 
            array (
                'id' => 69,
                'type' => 'Pengeluaran',
                'sector_id' => 54,
                'code' => 'b',
            'name' => 'Impor Barang dan Jasa (LN + AP)',
            ),
        ));
        
        
    }
}