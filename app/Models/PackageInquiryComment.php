<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageInquiryComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'package_inquiry_comments';
    protected $guarded = [];
}

