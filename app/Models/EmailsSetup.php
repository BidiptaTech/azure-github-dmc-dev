<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailsSetup extends Model
{
    protected $table = 'emails_setup';

    protected $fillable = [
        'dmcId',
        'From_Email',
        'From_Name',
        'SMTP_Host',
        'SMTP_Port',
        'SMTP_Encrypt',
        'SMTP_User',
        'SMTP_Pass',
        'IMAP_Host',
        'IMAP_Port',
        'IMAP_Encrypt',
        'IMAP_User',
        'IMAP_Pass',
        'support_email',
        'support_phone',
        'email_footer',
        'created_By',
    ];

    protected $casts = [
        'dmcId' => 'integer',
        'SMTP_Port' => 'integer',
        'IMAP_Port' => 'integer',
        'created_By' => 'integer',
    ];
}
