<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachmentFile extends Model
{
    protected $fillable = [
        'attachment_id',
        'file_name',
        'file_path',
        'mime_type',
        'size',
    ];

    /**
     * Get the attachment that owns the file.
     */
    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class);
    }
}
