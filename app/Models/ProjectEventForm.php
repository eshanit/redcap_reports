<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectEventForm extends Model
{
    use HasFactory;

    protected $table = 'redcap_events_forms';

    
    public function redcap_events_metadata(): HasMany
    {
        return $this->hasMany(ProjectEventMetadata::class);
    }

}
