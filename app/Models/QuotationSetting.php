<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationSetting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'quotation_settings';
    protected $guarded = [];

    protected $casts = [
        'dmc_id' => 'integer',
        'quotation_setting_id' => 'integer',
    ];
}

