<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;


class ProjectMetadata extends Model
{
    use HasFactory;

    protected $table = 'redcap_metadata';


    //
    public function projects(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }


    //Mutator
    public function formName(): Attribute
    {
        return Attribute::make(
             fn ($value) => Str::headline($value)
        );
    }

      /**
   * Mutator
   */
  public function elementEnum(): Attribute
  {
    return Attribute::make(
      fn($value) => Str::replace('\n','<br />',$value)
    );
  }

    ///defining many to many

    // public function project_event_metadata(): BelongsToMany
    // {
    //   return $this->belongsToMany(ProjectEventMetadata::class,'redcap_events_forms','form_name', 'event_id');
    // }
  
}
