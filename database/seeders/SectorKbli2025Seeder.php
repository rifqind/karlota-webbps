<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorKbli2025Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapping category_id input ke kode Category untuk fleksibilitas environment
        $categoryCodeMap = [
            19 => 'A',
            20 => 'B',
            21 => 'C',
            22 => 'D',
            23 => 'E',
            24 => 'F',
            25 => 'G',
            26 => 'H',
            27 => 'I',
            28 => 'J',
            29 => 'K',
            30 => 'L',
            31 => 'M',
            32 => 'N',
            33 => 'O',
            34 => 'P',
            35 => 'Q',
            36 => 'R',
            37 => 'S',
            38 => 'T,U,V',
            39 => 'Y',
        ];

        $categories = DB::table('categories')
            ->where('kbli', 'kbli_2025')
            ->pluck('id', 'code')
            ->toArray();

        $sectors = [
            ['kbli' => 'kbli_2025', 'category_id' => 19, 'code' => '1', 'name' => 'Pertanian Tanaman, Peternakan, Perburuan, dan Kegiatan Jasa Terkait'],
            ['kbli' => 'kbli_2025', 'category_id' => 19, 'code' => '2', 'name' => 'Pengelolaan Kehutanan dan Pemanenan Kayu'],
            ['kbli' => 'kbli_2025', 'category_id' => 19, 'code' => '3', 'name' => 'Perikanan'],
            ['kbli' => 'kbli_2025', 'category_id' => 20, 'code' => '1', 'name' => 'Pertambangan Minyak Bumi, Gas Alam, dan Panas Bumi'],
            ['kbli' => 'kbli_2025', 'category_id' => 20, 'code' => '2', 'name' => 'Pertambangan Batu Bara dan Lignit'],
            ['kbli' => 'kbli_2025', 'category_id' => 20, 'code' => '3', 'name' => 'Pertambangan Bijih Logam'],
            ['kbli' => 'kbli_2025', 'category_id' => 20, 'code' => '4', 'name' => 'Pertambangan dan Penggalian Lainnya'],
            ['kbli' => 'kbli_2025', 'category_id' => 21, 'code' => '1', 'name' => 'Industri Produk dari Batu Bara dan Pengilangan Minyak Bumi'],
            ['kbli' => 'kbli_2025', 'category_id' => 21, 'code' => '2', 'name' => 'Industri Non Migas'],
            ['kbli' => 'kbli_2025', 'category_id' => 22, 'code' => '1', 'name' => 'Penyediaan Listrik'],
            ['kbli' => 'kbli_2025', 'category_id' => 22, 'code' => '2', 'name' => 'Penyediaan Gas, Uap/Air Panas, dan Udara Dingin'],
            ['kbli' => 'kbli_2025', 'category_id' => 23, 'code' => '1', 'name' => 'Penyediaan Air'],
            ['kbli' => 'kbli_2025', 'category_id' => 23, 'code' => '2', 'name' => 'Pengelolaan Limbah dan Aktivitas Remediasi'],
            ['kbli' => 'kbli_2025', 'category_id' => 24, 'code' => null, 'name' => 'Konstruksi'],
            ['kbli' => 'kbli_2025', 'category_id' => 25, 'code' => '1', 'name' => 'Perdagangan Besar'],
            ['kbli' => 'kbli_2025', 'category_id' => 25, 'code' => '2', 'name' => 'Perdagangan Eceran'],
            ['kbli' => 'kbli_2025', 'category_id' => 26, 'code' => '1', 'name' => 'Transportasi Kereta Api'],
            ['kbli' => 'kbli_2025', 'category_id' => 26, 'code' => '2', 'name' => 'Transportasi Jalan dan Transportasi melalui Saluran Pipa'],
            ['kbli' => 'kbli_2025', 'category_id' => 26, 'code' => '3', 'name' => 'Transportasi Perairan'],
            ['kbli' => 'kbli_2025', 'category_id' => 26, 'code' => '4', 'name' => 'Transportasi Udara'],
            ['kbli' => 'kbli_2025', 'category_id' => 26, 'code' => '5', 'name' => 'Pergudangan dan Penunjang Transportasi'],
            ['kbli' => 'kbli_2025', 'category_id' => 26, 'code' => '6', 'name' => 'Aktivitas Pos dan Kurir'],
            ['kbli' => 'kbli_2025', 'category_id' => 27, 'code' => '1', 'name' => 'Penyediaan Akomodasi'],
            ['kbli' => 'kbli_2025', 'category_id' => 27, 'code' => '2', 'name' => 'Penyediaan Makanan dan Minuman'],
            ['kbli' => 'kbli_2025', 'category_id' => 28, 'code' => '1', 'name' => 'Penerbitan, Penyiaran, Serta Produksi dan Distribusi Konten Pemerintah'],
            ['kbli' => 'kbli_2025', 'category_id' => 28, 'code' => '2', 'name' => 'Penerbitan, Penyiaran, Serta Produksi dan Distribusi Konten Swasta'],
            ['kbli' => 'kbli_2025', 'category_id' => 29, 'code' => null, 'name' => 'Telekomunikasi, Pemrograman Komputer, Konsultasi, Infrastruktur Komputasi, dan Informasi Lainnya'],
            ['kbli' => 'kbli_2025', 'category_id' => 30, 'code' => '1', 'name' => 'Aktivitas Intermediasi Keuangan'],
            ['kbli' => 'kbli_2025', 'category_id' => 30, 'code' => '2', 'name' => 'Aktivitas Keuangan Lainnya, Kecuali Asuransi dan Dana Pensiun'],
            ['kbli' => 'kbli_2025', 'category_id' => 30, 'code' => '3', 'name' => 'Aktivitas Asuransi, Reasuransi, Penjaminan, dan Dana Pensiun'],
            ['kbli' => 'kbli_2025', 'category_id' => 30, 'code' => '4', 'name' => 'Aktivitas Penunjangan Keuangan'],
            ['kbli' => 'kbli_2025', 'category_id' => 31, 'code' => null, 'name' => 'Real Estat'],
            ['kbli' => 'kbli_2025', 'category_id' => 32, 'code' => null, 'name' => 'Aktivitas Profesional, Ilmiah, dan Teknis'],
            ['kbli' => 'kbli_2025', 'category_id' => 33, 'code' => null, 'name' => 'Aktivitas Administratif dan Penunjang Usaha'],
            ['kbli' => 'kbli_2025', 'category_id' => 34, 'code' => null, 'name' => 'Administrasi Pemerintahan dan Pertahanan; Jaminan Sosial Wajib'],
            ['kbli' => 'kbli_2025', 'category_id' => 35, 'code' => '1', 'name' => 'Pendidikan Pemerintah'],
            ['kbli' => 'kbli_2025', 'category_id' => 35, 'code' => '2', 'name' => 'Pendidikan Swasta Market'],
            ['kbli' => 'kbli_2025', 'category_id' => 35, 'code' => '3', 'name' => 'Pendidikan Swasta Nonmarket'],
            ['kbli' => 'kbli_2025', 'category_id' => 36, 'code' => '1', 'name' => 'Aktivitas Kesehatan Pemerintah'],
            ['kbli' => 'kbli_2025', 'category_id' => 36, 'code' => '2', 'name' => 'Aktivitas Kesehatan Swasta Market'],
            ['kbli' => 'kbli_2025', 'category_id' => 36, 'code' => '3', 'name' => 'Aktivitas Kesehatan Swasta Nonmarket'],
            ['kbli' => 'kbli_2025', 'category_id' => 37, 'code' => '1', 'name' => 'Aktivitas Kesenian, Olahraga dan Rekreasi Pemerintah'],
            ['kbli' => 'kbli_2025', 'category_id' => 37, 'code' => '2', 'name' => 'Aktivitas Kesenian, Olahraga dan Rekreasi Lainnya Swasta Market'],
            ['kbli' => 'kbli_2025', 'category_id' => 37, 'code' => '3', 'name' => 'Aktivitas Kesenian, Olahraga dan Rekreasi Lainnya Swasta Nonmarket'],
            ['kbli' => 'kbli_2025', 'category_id' => 38, 'code' => '1', 'name' => 'Aktivitas Lainnya Swasta Market'],
            ['kbli' => 'kbli_2025', 'category_id' => 38, 'code' => '2', 'name' => 'Aktivitas Lainnya Swasta Nonmarket'],
            ['kbli' => 'kbli_2025', 'category_id' => 38, 'code' => '3', 'name' => 'Aktivitas Lainnya Own Consumption'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '1', 'name' => 'Pengeluaran Konsumsi Rumah Tangga (1.a. s/d 1.m.)'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '2', 'name' => 'Pengeluaran Konsumsi LNPRT'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '3', 'name' => 'Pengeluaran Konsumsi Pemerintah (3.a. + 3.b.)'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '4', 'name' => 'Pembentukan Modal Tetap Bruto (4.a. + 4.b.)'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '5', 'name' => 'Perubahan Inventori'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '6', 'name' => 'Ekspor Luar Negeri (6.a. + 6.b.)'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '7', 'name' => 'Impor Luar Negeri (7.a. + 7.b.)'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '8', 'name' => 'Ekspor Antar Provinsi (8.a. + 8.b.)'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '9', 'name' => 'Impor Antar Provinsi (9.a. + 9.b.)'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '10', 'name' => 'Ekspor Antar Kabupaten/Kota (10.a. + 10.b.)'],
            ['kbli' => 'kbli_2025', 'category_id' => 39, 'code' => '11', 'name' => 'Impor Antar Kabupaten/Kota (11.a. + 11.b.)'],
        ];

        foreach ($sectors as $sector) {
            $catCode = $categoryCodeMap[$sector['category_id']] ?? null;
            $resolvedCategoryId = ($catCode && isset($categories[$catCode]))
                ? $categories[$catCode]
                : $sector['category_id'];

            DB::table('sectors')->updateOrInsert(
                [
                    'kbli' => $sector['kbli'],
                    'category_id' => $resolvedCategoryId,
                    'name' => $sector['name'],
                ],
                [
                    'code' => $sector['code'],
                ]
            );
        }
    }
}
