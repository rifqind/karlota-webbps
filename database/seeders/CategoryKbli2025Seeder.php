<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryKbli2025Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'A',
                'name' => 'Pertanian, Kehutanan, dan Perikanan',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'B',
                'name' => 'Pertambangan dan Penggalian',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'C',
                'name' => 'Industri',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'D',
                'name' => 'Penyediaan Listrik, Gas, Uap/Air Panas, dan Udara Dingin',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'E',
                'name' => 'Penyediaan Air, Pengelolaan Limbah, Penanganan Limbah dan Remediasi',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'F',
                'name' => 'Konstruksi',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'G',
                'name' => 'Perdagangan Besar dan Eceran',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'H',
                'name' => 'Transportasi dan Penyimpanan',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'I',
                'name' => 'Penyediaan Akomodasi dan Makan Minum',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'J',
                'name' => 'Penerbitan, Penyiaran, Serta Produksi, dan Distribusi Konten',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'K',
                'name' => 'Telekomunikasi, Pemrograman Komputer, Konsultasi, Infrastruktur Komputasi, dan Informasi Lainnya',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'L',
                'name' => 'Jasa Keuangan dan Asuransi',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'M',
                'name' => 'Real Estat',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'N',
                'name' => 'Aktivitas Profesional, Ilmiah, dan Teknis',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'O',
                'name' => 'Aktivitas Administratif dan Penunjang Usaha',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'P',
                'name' => 'Administrasi Pemerintahan dan Pertahanan; Jaminan Sosial Wajib',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'Q',
                'name' => 'Pendidikan',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'R',
                'name' => 'Aktivitas Kesehatan dan Sosial',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'S',
                'name' => 'Aktivitas Kesenian Olahraga dan Rekreasi',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Lapangan Usaha',
                'code' => 'T,U,V',
                'name' => 'Aktivitas Lainnya',
            ],
            [
                'kbli' => 'kbli_2025',
                'type' => 'Pengeluaran',
                'code' => 'Y',
                'name' => 'Pengeluaran',
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                [
                    'kbli' => $category['kbli'],
                    'code' => $category['code'],
                ],
                [
                    'type' => $category['type'],
                    'name' => $category['name'],
                ]
            );
        }
    }
}
