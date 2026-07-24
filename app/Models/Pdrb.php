<?php

namespace App\Models;

use App\HasJobLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pdrb extends Model
{
    use HasFactory, HasJobLogs;

    protected $guarded = ['id'];

    protected $with = [
        'dataset',
        'subsector',
        //  'adjustment'
    ];
    // protected $load = ['adjustment'];

    public $timestamps = false;


    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function subsector()
    {
        return $this->belongsTo(Subsector::class);
    }

    public function adjustment()
    {
        return $this->hasOne(Adjustment::class);
    }

    public function category()
    {
        return $this->subsector->sector->category_id ?? null;
    }

    public function scopeWithAdjustmentsAndDatasets($query)
    {
        return $query->leftJoin('adjustments as adj', 'adj.pdrb_id', '=', 'pdrbs.id')
                     ->join('datasets as d', 'd.id', '=', 'pdrbs.dataset_id');
    }

    public function scopeSelectTotalPdrb($query)
    {
        return $query->selectRaw(
            'pdrbs.year,
            pdrbs.quarter,
            SUM(pdrbs.adhb) as adhb,
            SUM(pdrbs.adhk) as adhk,
            SUM(adj.adhb) as adj_adhb,
            SUM(adj.adhk) as adj_adhk,
            d.region_id as region_id'
        );
    }

    public function scopeSelectTotalPdrbProvinsi($query)
    {
        return $query->selectRaw(
            'pdrbs.year,
            pdrbs.quarter,
            SUM(pdrbs.adhb) as adhb,
            SUM(pdrbs.adhk) as adhk,
            SUM(adj.adhb) as adj_adhb,
            SUM(adj.adhk) as adj_adhk'
        );
    }
}
