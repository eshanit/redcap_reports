<?php

namespace App\Http\Controllers\Customized\NCD;

// ini_set('memory_limit', '512M');
// ini_set('max_execution_time', 300);

use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Inertia\Inertia;

class AppointmentsReviewControllerFancy2 extends Controller
{

    public function __invoke(string $id)
    {
        $project = Project::select('project_id', 'app_title')->findOrFail($id);

        // Fetch data using a join for efficiency
        $results = ProjectData::select('redcap_data.*', 'redcap_events_metadata.descrip as event')
            ->join('redcap_events_metadata', 'redcap_events_metadata.event_id', '=', 'redcap_data.event_id')
            ->where('project_id', $id)
            ->whereIn('field_name', [
                'ncd_visit_date',
                'ncd_next_review',
                'ncd_tel_pat',
                'ncd_tel_kin',
                'ncd_health_facility'
            ])
            ->get()
            ->groupBy('record');

        // Process results
        $resultXY = $results->map(function ($eventGroups) {
            return $this->processEventGroup($eventGroups);
        });

        // Get additional data
        $statusDistribution = $this->getStatusDistribution($resultXY);
        $upcomingAppointments = $this->getUpcomingAppointments($resultXY);
        $lateAppointments = $this->getLateAppointments($resultXY);
        $trends = $this->getRecordSpecificTrends($resultXY);
        $latestVisits = $this->getLatestVisit($resultXY);
        $defaulters = $this->getDefaultedRecords($resultXY);

        return Inertia::render('Customized/NCD/ReviewDatesAllSites', [
            'project' => $project,
            'data' => $resultXY,
            'dataCounts' => $trends,
            'latestData' => $latestVisits,
            'statusDistribution' => $statusDistribution,
            'upcomingAppointments' => $upcomingAppointments,
            'lateAppointments' => $lateAppointments,
            'defaulters' => $defaulters,
        ]);
    }

    private function processEventGroup($eventGroups)
    {
        // Initialize arrays for proposed and actual dates
        $data = [
            'event_id' => $eventGroups->pluck('event_id')->first(),
            'event' => $eventGroups->pluck('event')->first(),
            'ncd_health_facility' => $eventGroups->where('field_name', 'ncd_health_facility')->pluck('value')->first(),
            'ncd_tel_pat' => $eventGroups->where('field_name', 'ncd_tel_pat')->pluck('value')->first(),
            'ncd_tel_kin' => $eventGroups->where('field_name', 'ncd_tel_kin')->pluck('value')->first(),
            'proposed_dates' => $eventGroups->where('field_name', 'ncd_next_review')->pluck('value')->prepend('')->push(''),
            'actual_dates' => $eventGroups->where('field_name', 'ncd_visit_date')->pluck('value')->prepend('')->push(''),
        ];

        $data['days_difference'] = $this->calculateDateDifferences($data['proposed_dates'], $data['actual_dates']);
        $data['status'] = $this->calculateStatus($data['proposed_dates'], $data['actual_dates']);

        return $data;
    }

    private function calculateDateDifferences($proposedDates, $actualDates)
    {
        return $proposedDates->zip($actualDates)->map(function ($pair) {
            [$proposed, $actual] = $pair;
            return $proposed && $actual ? Carbon::parse($proposed)->diffInDays(Carbon::parse($actual)) : null;
        });
    }

    private function calculateStatus($proposedDates, $actualDates)
    {
        return $proposedDates->zip($actualDates)->map(function ($pair) {
            [$proposed, $actual] = $pair;
            if ($actual && $proposed) {
                return $actual > $proposed ? 'Late' : ($actual == $proposed ? 'On Time' : 'Early');
            }
            return '-';
        });
    }

    private function getStatusDistribution($results)
    {
        return collect($results)->pluck('status')->flatten()->countBy();
    }

    private function getUpcomingAppointments($results)
    {
        return $results->filter(function ($data) {
            return Carbon::parse($data['proposed_dates']->last())->greaterThan(Carbon::today());
        })->map(function ($data) {
            return [
                'event_id' => $data['event_id'],
                'status' => 'Pending',
                'days_difference' => Carbon::parse($data['proposed_dates']->last())->diffForHumans(),
            ];
        });
    }

    private function getLatestVisit($results)
    {
        return $results->map(function ($data) {
            return [
                'event_id' => $data['event_id'],
                'event' => $data['event'],
                'facility' => $data['ncd_health_facility'],
                'proposed_dates' => $data['proposed_dates']->last(),
                'actual_dates' => $data['actual_dates']->last(),
            ];
        });
    }

    private function getLateAppointments($results)
    {
        return $results->filter(function ($data) {
            return in_array('Late', $data['status']->toArray()); // Convert Collection to array
        });
    }

    private function getRecordSpecificTrends($results)
    {
        return $results->map(function ($data) {
            return [
                'Late Visits' => collect($data['status'])->where('Late')->count(),
                'Early Visits' => collect($data['status'])->where('Early')->count(),
                'On Time Visits' => collect($data['status'])->where('On Time')->count(),
                'No Data' => collect($data['status'])->where('-')->count(),
            ];
        });
    }

    private function getDefaultedRecords($results)
    {
        return $results->filter(function ($data) {
            return Carbon::parse($data['proposed_dates']->last())->isPast();
        })->map(function ($data) {
            return [
                'last_event' => $data['event'],
                'facility' => $data['ncd_health_facility'],
                'proposed_appointment_date' => $data['proposed_dates']->last(),
                'statusDefault' => 'Missed Appointment',
            ];
        });
    }
}
