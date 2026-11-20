<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BedMaster extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'beds_master'; 
    protected $guarded = [];

    public function hotel(){
        return $this->belongsTo(Hotel::class, 'hotel_id', 'hotel_unique_id');
    }

    public function adultOccupancy(): int
    {
        return ((int) ($this->no_of_king_bed ?? 0) * 2)
            + ((int) ($this->no_of_queen_bed ?? 0) * 2)
            + ((int) ($this->no_of_twin_bed ?? 0) * 2)
            + (int) ($this->no_of_single_bed ?? 0)
            + ((int) ($this->no_of_bunk_bed ?? 0) * 2);
    }

    public function childWithoutBedOccupancy(): int
    {
        return ((int) ($this->child_wo_bed ?? 0)) > 0 ? 1 : 0;
    }

    public function totalOccupancy(): int
    {
        return $this->adultOccupancy() + $this->childWithoutBedOccupancy();
    }
}
