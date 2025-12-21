<?php

namespace Feraandrei1\FilamentDynamicSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'uploaded_by_user_id',
        'group',
        'name',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'uploaded_by_user_id');
    }
}
