<?php

namespace App\Models;

use App\Helpers\CommonHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'settings'; 
    protected $guarded = []; 

    public static function getCurrencyCodes()
    {
        return CommonHelper::getPaymentAvailableCurrencies();
    }

}
