<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    
    protected $table = 'redcap_projects';
    protected $primaryKey = 'project_id';

    public function project_data(): HasMany
    {
        return $this->hasMany(ProjectData::class,'project_id');
    }


    public function projectDataCounts()
    {
        return $this->hasOne(ProjectData::class,'project_id')
                   ->selectRaw('project_id, count(*) as aggregate')
                   ->groupBy('project_id');
    }


    public function getProjectDataCounts()
    {
         // if relation is not loaded already, let's do it first
        if( !array_key_exists('projectDataCounts', $this->relations))
            $this->load('projectDataCounts');

            $related = $this->getRelation('projectDataCounts');

            // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }

    //
    public function project_events_arms(): HasMany
    {
        return $this->hasMany(ProjectEventArm::class,'project_id');
    }

    //
    public function project_templates(): HasOne
    {
        return $this->hasOne(ProjectTemplates::class);
    }

//removed has Many 
    public function project_metadata(): HasMany
    {
        return $this->hasMany(ProjectMetadata::class, 'project_id');
    }
 

//
public function scopeOrderByName($query)
{
    $query->orderBy('app_title');
}

//
public function scopeFilter($query, array $filters)
{
    $query->when($filters['search'] ?? null, function ($query, $search) {
        $query->where(function ($query) use ($search) {
            $query->where('app_title', 'like', '%'.$search.'%');
        });
    })->when($filters['trashed'] ?? null, function ($query, $trashed) {
        if ($trashed === 'with') {
            $query->withTrashed();
        } elseif ($trashed === 'only') {
            $query->onlyTrashed();
        }
    });
}

}
