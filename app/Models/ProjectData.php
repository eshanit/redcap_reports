<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ProjectData extends Model
{
    use HasFactory;

    protected $table = 'redcap_data';


    public function respondent()
    {
        return $this->belongsTo(Respondent::class, 'record', 'record');
    }

    public function projects(): BelongsTo
    {
        return $this->belongsTo(Project::class,'project_id');
    }

      //

  //
  public function project_event_metadata(): BelongsTo
  {
      return $this->belongsTo(ProjectEventMetadata::class, 'event_id');
  }

 /**
     * 
     */
    public function project_metadata(): HasManyThrough
    {
        return $this->hasManyThrough(ProjectMetadata::class,Project::class,'project_id','project_id' );
    }


    //scopes


    public function scopeFilterByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeFilterByField($query, $fieldName, $value)
    {
        return $query->where('field_name', $fieldName)
                    ->where('value', $value);
    }

    public function scopeFilterByEventAndRecord($query, $eventId, $record)
    {
        return $query->where('event_id', $eventId)
                    ->where('record', $record);
    }

    //search scopes
    //
public function scopeOrderByEvent($query)
{
    $query->orderBy('event_id');
}

//
public function scopeFilter($query, array $filters)
{
    $query->when($filters['search'] ?? null, function ($query, $search) {
        $query->where(function ($query) use ($search) {
            $columns = ['record', 'event_id', 'field_name', 'value']; // Add any other columns you want to search
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', '%' . $search . '%');
            }
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
