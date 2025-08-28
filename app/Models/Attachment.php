<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the parent attachable model.
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the attachment files for the attachment.
     */
    public function files(): HasMany
    {
        return $this->hasMany(AttachmentFile::class);
    }

    /**
     * Get the user that created the attachment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the attachment.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attachment) {
            if (Auth::check()) {
                $attachment->created_by = Auth::id();
            }
        });

        static::updating(function ($attachment) {
            if (Auth::check()) {
                $attachment->updated_by = Auth::id();
            }
        });
    }
}
