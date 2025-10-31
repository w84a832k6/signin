<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'event_date',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
