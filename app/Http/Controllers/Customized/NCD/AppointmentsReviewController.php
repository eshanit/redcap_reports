<?php

namespace App\Http\Controllers\Customized\NCD;

use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Inertia\Inertia;

class AppointmentsReviewController extends Controller
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
            ->whereIn('field_name', ['ncd_visit_date', 'ncd_next_review', 'ncd_tel_pat', 'ncd_tel_kin'])
            ->get()
            ->groupBy('record');

        $results = [];
        $resultsCount = [];
        $latestResults = [];
        $recordsWithNextReview = []; // Initialize an array to hold records with next reviews

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

            // Initialize variables for telephone numbers
            $telPat = null;
            $telKin = null;

            // Extract the visit and next review dates
            foreach ($data as $entry) {
                if ($entry->field_name === 'ncd_visit_date') {
                    $visits[$entry->event_id] = [
                        'date' => Carbon::parse($entry->value),
                        'event' => $entry->event // Store the event description here
                    ];
                } elseif ($entry->field_name === 'ncd_next_review') {
                    $nextReviews[$entry->event_id] = Carbon::parse($entry->value);
                } elseif ($entry->field_name === 'ncd_tel_pat') {
                    $telPat = $entry->value; // Store patient's telephone
                } elseif ($entry->field_name === 'ncd_tel_kin') {
                    $telKin = $entry->value; // Store kin's telephone
                }
            }

            // Check if there are visits
            if (empty($visits)) {
                continue; // Skip this record if there are no visits
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

                    /****new */
                    // Check the latest visit against the previous next review
                    foreach ($visits as $eventId => $visitData) {
                        // Existing logic to determine status...

                        // After determining the status, track the last event
                        $lastEventId = array_keys($visits)[count($visits) - 1]; // Get the last event ID
                        $lastEventDescription = $visitData['event'];

                        // Check if the last visit has a corresponding next review
                        // if (array_key_exists($lastEventId, $nextReviews)) {
                        //     $recordsWithNextReview[$record] = [
                        //         'last_event' => $lastEventDescription,
                        //         'next_review_date' => $nextReviews[$lastEventId]->toDateString(),
                        //     ];
                        // }
                        // Check if the next review date has already passed
                        if ($nextReviewDate->isPast()) {
                            // Calculate the days difference
                            $daysDifferenceNow = $nextReviewDate->diffForHumans();

                            $recordsWithNextReview[$record] = [
                                'last_event' => $lastEventDescription,
                                'proposed_appointment_date' => $nextReviewDate->toDateString(),
                                'actual_visit_date' => 'DNC',
                                'status' => 'Late',
                                'days_difference' => $daysDifferenceNow,
                                'ncd_tel_pat' => $telPat ?? '-', // Add patient's telephone
                                'ncd_tel_kin' => $telKin ?? '-', // Add kin's telephone
                            ];
                        }
                    }
                    /***new */

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
        }

        //dd($latestBeforeToday, $latestResults);
        //Full dataset
        $statusDistribution = $this->getStatusDistribution($results);
        $averageDaysDifference = $this->getAverageDaysDifference($results);
        $upcomingAppointments = $this->getUpcomingAppointments($results);
        $lateAppointments = $this->getLateAppointments($results);
        $eventAnalysis = $this->getEventSpecificAnalysis($results);
        $trends = $this->getRecordSpecificTrends($results);


        return Inertia::render(
            'Customized/NCD/ReviewDates',
            [
                'project' => $project,
                'data' => $results,
                'dataCounts' => $resultsCount,
                'latestData' => $latestResults,
                'statusDistribution' => $statusDistribution,
                'averageDaysDifference' => $averageDaysDifference,
                'upcomingAppointments' => $upcomingAppointments,
                'lateAppointments' => $lateAppointments,
                'eventAnalysis' => $eventAnalysis,
                'trends' => $trends,
                'defaulters' => $recordsWithNextReview
            ]
        );
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
                $statusCounts[$entry['status']]++;
            }
        }

        return $statusCounts;
    }


    /**
     * Average days diffence
     */
    private function getAverageDaysDifference(array $results)
    {
        $totalDays = 0;
        $count = 0;

        foreach ($results as $recordData) {
            foreach ($recordData as $entry) {
                if ($entry['days_difference'] !== null) {
                    $days = Carbon::parse($entry['actual_visit_date'])
                        ->diffInDays(Carbon::parse($entry['proposed_appointment_date']));
                    $totalDays += $days;
                    $count++;
                }
            }
        }

        return $count > 0 ? $totalDays / $count : 0;
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
                    $upcomingAppointments[$record][$event] = $entry;
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
                $trends[$record][] = $entry['status'];
            }
        }

        return $trends;
    }
}
