<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Respondent extends Model
{
    use HasFactory;

    protected $table = 'redcap_data';
    protected $primaryKey = 'record';

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class,'project_id','project_id');
    }

    public function data()
    {
        return $this->hasMany(ProjectData::class, 'record', 'record');
    }

}
