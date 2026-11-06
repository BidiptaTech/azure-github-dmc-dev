<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTemplateVersion extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'invoice_template_versions';

    protected $fillable = [
        'invoice_template_id',
        'version',
        'html_content',
        'css_content',
        'status',
        'change_notes',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'invoice_template_id' => 'integer',
        'version' => 'integer',
        'created_by' => 'integer',
        'published_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'userId');
    }
}
