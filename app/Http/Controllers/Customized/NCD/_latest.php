<?php

namespace App\Http\Controllers\Customized\NCD;

use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AppointmentsReviewController_latest extends Controller
{
    /**
     * Handle the incoming request.
     */
    /**
     * Handle the incoming request.
     */
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $id)
    {
        // Fetch the project
        $project = Project::select('project_id', 'app_title')->findOrFail($id);

        $results = ProjectData::where('project_id', $id)
        ->addSelect([
            'event' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data.event_id')
        ])
        ->whereIn('field_name', ['ncd_visit_date', 'ncd_next_review', 'ncd_tel_pat', 'ncd_tel_kin'])
        ->get()
        ->groupBy('record')
        ->map(function ($group) {
            return $group->groupBy('event_id')->map(function ($eventGroup) {
                return [
                    'event_id' => $eventGroup->pluck('event_id')->first(),
                    'ncd_visit_date' => $eventGroup->where('field_name', 'ncd_visit_date')->pluck('value')->first(),
                    'ncd_next_review' => $eventGroup->where('field_name', 'ncd_next_review')->pluck('value')->first(),
                    'ncd_tel_pat' => $eventGroup->where('field_name', 'ncd_tel_pat')->pluck('value')->first(),
                    'ncd_tel_kin' => $eventGroup->where('field_name', 'ncd_tel_kin')->pluck('value')->first(),
                ];
            });
        });
    
    // Get event IDs
    $resultsEvents = $results->map(function ($eventGroups) {
        return $eventGroups->pluck('event_id');
    });
    
    // Get proposed dates
    $resultsProposedDates = $results->map(function ($eventGroups) {
        return $eventGroups->map(function ($group) {
            $clonedGroup = collect($group);
            $clonedGroup->shift(); // Remove the first element
            $clonedGroup->pop();   // Remove the last element
            
            return $clonedGroup->pluck('ncd_next_review')->prepend('')->push(''); // Proposed dates
        });
    });
    
    // Get actual dates
    $resultsActualDates = $results->map(function ($eventGroups) {
        return $eventGroups->map(function ($group) {
            $clonedGroup = collect($group);
            $clonedGroup->shift(2); // Remove the first 2 elements
    
            return $clonedGroup->pluck('ncd_visit_date')->prepend('')->push(''); // Actual dates
        });
    });
    
    // // Create resultXY with status
    $resultXY = $resultsEvents->map(function ($item, $index) use ($resultsProposedDates, $resultsActualDates) {
        $proposedDates = $resultsProposedDates[$index];
        $actualDates = $resultsActualDates[$index];
    
        $statuses = [];
    
        foreach ($proposedDates as $key => $proposedDate) {
            $actualDate = $actualDates[$key];
    //dd($actualDate, $proposedDate);
            // Compare dates
            if($proposedDate != null || $proposedDate != "") {
                $proposedDateCarbon = Carbon::parse($proposedDate);
            }

            if($actualDate != null || $proposedDate != "") {
                $actualDateCarbon = Carbon::parse($actualDate);
            }
            
           
  
            if ($proposedDateCarbon < $actualDateCarbon) {
                $statuses[] = 'Late';
            } elseif ($proposedDateCarbon > $actualDateCarbon) {
                $statuses[] = 'Early';
            } elseif ($proposedDateCarbon = $actualDateCarbon){
                $statuses[] = 'On Time';
            } else {
                $statuses[] = 'No Data';
            }
        }
    
        return [
            'event_id' => $item->toArray(),
            'proposed_dates' => $proposedDates->toArray(),
            'actual_dates' => $actualDates->toArray(),
            'status' => $statuses, // Add status array
        ];
    });

    dd( $resultsEvents['CHK0001'], $resultsProposedDates['CHK0001'], $resultsActualDates['CHK0001']);

    }    



    private function determineStatus($visitDate, $nextReviewDate)
    {
        if ($visitDate->greaterThan($nextReviewDate)) {
            return 'Late';
        } elseif ($visitDate->lessThan($nextReviewDate)) {
            return 'Not Late';
        } elseif ($visitDate->isSameDay($nextReviewDate)) {
            return 'On Time';
        }
        return '-';
    }



    /**
     * Status distribution
     */

    private function getStatusDistribution(array $results)
    {
        $statusCounts = [
            'Late' => 0,
            'Not Late' => 0,
            'On Time' => 0,
            '-' => 0,
        ];

        foreach ($results as $recordData) {
            foreach ($recordData as $entry) {
                dd($entry);
                $statusCounts[$entry['status']]++;
            }
        }

        return $statusCounts;
    }


    /// get Upcoming visits

    private function getUpcomingAppointments(array $results)
    {
        $upcomingAppointments = [];

        foreach ($results as $record => $recordData) {
            foreach ($recordData as $event => $entry) {
                if (
                    $entry['proposed_appointment_date'] &&
                    Carbon::parse($entry['proposed_appointment_date'])->greaterThan(Carbon::today())
                ) {
                    //dd($entry['proposed_appointment_date']);
                    $daysDifferenceNow = Carbon::parse($entry['proposed_appointment_date'])->diffForHumans();
                    $upcomingAppointments[$record][$event] = $entry;
                    $upcomingAppointments[$record][$event]['days_difference'] =  $daysDifferenceNow;
                }
            }
        }

        return $upcomingAppointments;
    }

    ///late appointment analysis
    private function getLateAppointments(array $results)
    {
        $lateAppointments = [];

        foreach ($results as $record => $recordData) {
            foreach ($recordData as $event => $entry) {
                if ($entry['status'] === 'Late') {
                    $lateAppointments[$record][$event] = $entry;
                }
            }
        }

        return $lateAppointments;
    }

    /// event specific

    private function getEventSpecificAnalysis(array $results)
    {
        $eventStatusCounts = [];

        foreach ($results as $recordData) {
            foreach ($recordData as $event => $entry) {
                $status = $entry['status'];

                if (!isset($eventStatusCounts[$event])) {
                    $eventStatusCounts[$event] = [
                        'Late' => 0,
                        'Not Late' => 0,
                        'On Time' => 0,
                        '-' => 0,
                    ];
                }

                // Increment the count based on the status
                $eventStatusCounts[$event][$status]++;
            }
        }

        return $eventStatusCounts;
    }
    /// record trenda

    private function getRecordSpecificTrends(array $results)
    {
        $trends = [];

        foreach ($results as $record => $recordData) {
            $trends[$record] = [];
            foreach ($recordData as $entry) {
                $trends[$record][] = [
                    'status' => $entry['status'],
                    'date' => $entry['proposed_appointment_date'],
                ];
            }
        }

        return $trends;
    }
}
