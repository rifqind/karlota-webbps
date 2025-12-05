<?php

namespace App\Exports;

use App\Models\LK\Komoditas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IndeksHargaExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //
        $query = Komoditas::query();
        $query->leftJoin('indeks_harga as ih', 'ih.komoditas_id', '=', 'master_komoditas.id')
            ->selectRaw("
            master_komoditas.id,
            master_komoditas.label,
            master_komoditas.category_id,
            master_komoditas.sector_id,
            master_komoditas.subsector_id,
            ih.tahun,
            MAX(CASE WHEN ih.triwulan = 1 THEN ih.indeks_harga END) AS tw1,
            MAX(CASE WHEN ih.triwulan = 2 THEN ih.indeks_harga END) AS tw2,
            MAX(CASE WHEN ih.triwulan = 3 THEN ih.indeks_harga END) AS tw3,
            MAX(CASE WHEN ih.triwulan = 4 THEN ih.indeks_harga END) AS tw4
        ")
            ->groupBy(
                'master_komoditas.id',
                'master_komoditas.label',
                'master_komoditas.category_id',
                'master_komoditas.sector_id',
                'master_komoditas.subsector_id',
                'ih.tahun'
            );
        $query->orderBy('tahun', 'desc');
        $query->orderBy('category_id', 'asc')
            ->orderBy('sector_id', 'asc')
            ->orderBy('subsector_id', 'asc');
        $data = $query->get();
        $number = 1;
        foreach ($data as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        $data = $data->map(function ($item) {
            return [
                'no' => $item->number,
                'komoditas' => $item->label,
                'tahun' => $item->tahun,
                'tw1' => $item->tw1,
                'tw2' => $item->tw2,
                'tw3' => $item->tw3,
                'tw4' => $item->tw4
            ];
        });
        return $data;
    }

    public function headings(): array
    {
        return [
            'no',
            'komoditas',
            'tahun',
            'tw1',
            'tw2',
            'tw3',
            'tw4'
        ];
    }
}
