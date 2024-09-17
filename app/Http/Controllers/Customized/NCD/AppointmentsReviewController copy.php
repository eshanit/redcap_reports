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

class AppointmentsReviewControllerCP extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $id)
    {
        $project = Project::select('project_id', 'app_title')->findOrFail($id);

        // Raw SQL query to gather all necessary data
        $resultsx = DB::select("
        SELECT 
            pd.record,
            pd.event_id,
            em.descrip AS event_description,
            MAX(CASE WHEN pd.field_name = 'ncd_visit_date' THEN pd.value END) AS visit_date,
            MAX(CASE WHEN pd.field_name = 'ncd_next_review' THEN pd.value END) AS next_review_date,
            MAX(CASE WHEN pd.field_name = 'ncd_tel_pat' THEN pd.value END) AS tel_pat,
            MAX(CASE WHEN pd.field_name = 'ncd_tel_kin' THEN pd.value END) AS tel_kin
        FROM 
            redcap_data pd
        LEFT JOIN 
            redcap_events_metadata em ON pd.event_id = em.event_id
        WHERE 
            pd.project_id = ? 
            AND pd.field_name IN ('ncd_visit_date', 'ncd_next_review', 'ncd_tel_pat', 'ncd_tel_kin')
        GROUP BY 
            pd.record, pd.event_id, em.descrip
    ", [$id]);

        $results = [];
        $resultsCount = [];
        $latestResults = [];
        $recordsWithNextReview = [];

        foreach ($resultsx as $recordData) {
            $organizedResults[$recordData->record][$recordData->event_description] = $recordData;
        }

        foreach ($organizedResults as $record => $data) {
            // Get the specific visit and next review data
            $visits = [];
            $nextReviews = [];
            $statusCounts = [
                'Late' => 0,
                'Not Late' => 0,
                'On Time' => 0,
                '-' => 0,
            ];
            $telPat = null;
            $telKin = null;

            foreach ($data as $entry) {
                if (isset($entry->visit_date)) {
                    $visits[$entry->event_id] = Carbon::parse($entry->visit_date);
                }
                if (isset($entry->next_review_date)) {
                    $nextReviews[$entry->event_id] = Carbon::parse($entry->next_review_date);
                }
                if (isset($entry->tel_pat)) {
                    $telPat = $entry->tel_pat;
                }
                if (isset($entry->tel_kin)) {
                    $telKin = $entry->tel_kin;
                }
            }


            // Process visits and next reviews
            foreach ($visits as $eventDescription => $visitDate) {
                // dd($eventDescription);
                $previousEventDescription = $eventDescription - 1;
                $status = '-';

                if (isset($nextReviews[$previousEventDescription])) {
                    $nextReviewDate = $nextReviews[$previousEventDescription];

                    if ($nextReviewDate->greaterThan($visitDate)) {
                        $status = 'Late';
                        $statusCounts['Late']++;
                    } elseif ($nextReviewDate->equalTo($visitDate)) {
                        $status = 'On Time';
                        $statusCounts['Not Late']++;
                    } elseif ($nextReviewDate->lessThan($visitDate)) {
                        $status = 'Not Late';
                        $statusCounts['On Time']++;
                    }

                    /****new */
                    // Check the latest visit against the previous next review
                    foreach ($visits as $eventId => $visitDate) {
                        // Existing logic to determine status...

                        // After determining the status, track the last event
                        $lastEventId = $eventId-1; // Get the last event ID
                        $lastEventDescription = $eventId;

                        // Check if the next review date has already passed
                        if ($nextReviewDate->isPast()) {
                            // Calculate the days difference

                            $daysDifferenceNow = $nextReviewDate->diffForHumans();
                            $daysDiffNow = $nextReviewDate->diffInDays();


                            // dd();
                            // Calculate the number of days between proposed and actual dates
                            $daysDifference = $visitDate->diffForHumans($nextReviewDate);

                            $recordsWithNextReview[$record] = [
                                'last_event' => $lastEventDescription,
                                'proposed_appointment_date' => $nextReviewDate->toDateString(),
                                'actual_visit_date' => 'NS',
                                'status' => 'Late',
                                'statusDefault' => $daysDiffNow <= 183 ? 'Missed Appointment' : 'Defaulted',
                                'days_difference' => $daysDifferenceNow,
                                'ncd_tel_pat' => $telPat ?? '-', // Add patient's telephone
                                'ncd_tel_kin' => $telKin ?? '-', // Add kin's telephone
                            ];
                        }
                    }

                    $results[$record][$eventDescription] = [
                        'status' => $status,
                        'proposed_appointment_date' => $nextReviewDate->toDateString(),
                        'actual_visit_date' => $visitDate->toDateString(),
                        'days_difference' => $visitDate->diffInDays($nextReviewDate),
                    ];
                } else {
                    $results[$record][$eventDescription] = [
                        'status' => '-',
                        'proposed_appointment_date' => null,
                        'actual_visit_date' => $visitDate->toDateString(),
                        'days_difference' => null,
                    ];
                    $statusCounts['-']++;
                }

               // dd($results);
            }

             // Add the status counts to the results
             $resultsCount[$record]['status_counts'] = $statusCounts;

            // Collect records with next review if applicable
            if(isset($results[$record])) {
              $latestResults[$record][$eventDescription]  = Arr::last($results[$record]);

              $latestResults[$record][$eventDescription] = [];
            }
            
           
        }


        $statusDistribution = $this->getStatusDistribution($results);
        //dd($statusDistribution);
        //$averageDaysDifference = $this->getAverageDaysDifference($results);
        $upcomingAppointments = $this->getUpcomingAppointments($results);
        $lateAppointments = $this->getLateAppointments($results);
        $eventAnalysis = $this->getEventSpecificAnalysis($results);
        $trends = $this->getRecordSpecificTrends($results);

        return Inertia::render('Customized/NCD/ReviewDates', [
            'project' => $project,
            'data' => $results,
            'dataCounts' => $resultsCount,
            'latestData' => $latestResults,
            'statusDistribution' => $statusDistribution,
            //'averageDaysDifference' => $averageDaysDifference,
            'upcomingAppointments' => $upcomingAppointments,
            'lateAppointments' => $lateAppointments,
            'eventAnalysis' => $eventAnalysis,
            'trends' => $trends,
            'defaulters' => $recordsWithNextReview,
        ]);
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
