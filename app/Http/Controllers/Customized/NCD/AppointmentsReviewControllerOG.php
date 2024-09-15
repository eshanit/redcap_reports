<?php

namespace App\Http\Controllers\Customized\NCD;

use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Inertia\Inertia;

class AppointmentsReviewControllerOG extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $id)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($id);
        // Fetch data grouped by record
        $respondents = ProjectData::where('project_id', 59)->addSelect([
            'event' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data.event_id')
        ])
            ->whereIn('field_name', ['ncd_visit_date', 'ncd_next_review'])
            ->get()
            ->groupBy('record');

        $results = [];
        $resultsCount = [];
        $latestResults = [];
        $latestBeforeToday = []; // Initialize the latestBeforeToday array

        foreach ($respondents as $record => $data) {
            // Initialize arrays to hold visit and next review dates
            $visits = [];
            $nextReviews = [];
            $statusCounts = [
                'Late' => 0,
                'Not Late' => 0,
                'On Time' => 0,
                '-' => 0,
            ];

            // Extract the visit and next review dates
            foreach ($data as $entry) {
                if ($entry->field_name === 'ncd_visit_date') {
                    $visits[$entry->event_id] = [
                        'date' => Carbon::parse($entry->value),
                        'event' => $entry->event // Store the event description here
                    ];
                } elseif ($entry->field_name === 'ncd_next_review') {
                    $nextReviews[$entry->event_id] = Carbon::parse($entry->value);
                }
            }

            // Check the latest visit against the previous next review
            foreach ($visits as $eventId => $visitData) {
                $visitDate = $visitData['date'];
                $eventDescription = $visitData['event']; // Get the event description
                $prevEventId = $eventId - 1;

                if (array_key_exists($prevEventId, $nextReviews)) {
                    $nextReviewDate = $nextReviews[$prevEventId];
                    $status = '';

                    if ($visitDate->greaterThan($nextReviewDate)) {
                        $status = 'Late';
                        $statusCounts['Late']++;
                    } elseif ($visitDate->lessThan($nextReviewDate)) {
                        $status = 'Not Late';
                        $statusCounts['Not Late']++;
                    } elseif ($visitDate->isSameDay($nextReviewDate)) {
                        $status = 'On Time';
                        $statusCounts['On Time']++;
                    }

                    // Calculate the number of days between proposed and actual dates
                    $daysDifference = $visitDate->diffForHumans($nextReviewDate);

                    // Use event description instead of event ID
                    $results[$record][$eventDescription] = [
                        'status' => $status,
                        'proposed_appointment_date' => $nextReviewDate->toDateString(),
                        'actual_visit_date' => $visitDate->toDateString(),
                        'days_difference' => $daysDifference, // Add the days difference here
                    ];
                } else {
                    // If there's a visit but no next review for the same event
                    $results[$record][$eventDescription] = [
                        'status' => '-',
                        'proposed_appointment_date' => null,
                        'actual_visit_date' => $visitDate->toDateString(),
                        'days_difference' => null, // No difference if no next review
                    ];
                    $statusCounts['-']++;
                }
            }

            // Add the status counts to the results
            $resultsCount[$record]['status_counts'] = $statusCounts;

            // Get the latest result for the record
            $latestResults[$record][$eventDescription] = Arr::last($results[$record]);

            // Check if the proposed appointment date is greater than today
            if (
                isset($latestResults[$record][$eventDescription]['proposed_appointment_date']) &&
                Carbon::parse($latestResults[$record][$eventDescription]['proposed_appointment_date'])->greaterThan(Carbon::today())
            ) {
                $latestBeforeToday[$record] = $latestResults[$record];
            }
        }

        //dd($latestBeforeToday, $latestResults);

        return Inertia::render(
            'Customized/NCD/ReviewDates',
            [
                'project' => $project,
                'data' => $results,
                'dataCounts' => $resultsCount,
                'latestData' => $latestResults,
                'latestBeforeToday' => $latestBeforeToday // Pass the filtered data to the frontend
            ]
        );
    }
}
