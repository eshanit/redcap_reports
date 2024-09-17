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

class AppointmentsReviewController extends Controller
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
            ->whereIn('field_name', ['ncd_visit_date', 'ncd_next_review', 'ncd_tel_pat', 'ncd_tel_kin', 'ncd_facility'])
            ->get()
            ->groupBy('record')
            ->map(function ($group) {
                return $group->groupBy('event_id')->map(function ($eventGroup) {
                    return [
                        'event_id' => $eventGroup->pluck('event_id')->first(),
                        'event' => $eventGroup->pluck('event')->first(),
                        'ncd_visit_date' => $eventGroup->where('field_name', 'ncd_visit_date')->pluck('value')->first(),
                        'ncd_next_review' => $eventGroup->where('field_name', 'ncd_next_review')->pluck('value')->first(),
                        'ncd_tel_pat' => $eventGroup->where('field_name', 'ncd_tel_pat')->pluck('value')->first(),
                        'ncd_tel_kin' => $eventGroup->where('field_name', 'ncd_tel_kin')->pluck('value')->first(),
                    ];
                });
            });

        // Get proposed dates, shifting and popping elements
        $resultsEvents = $results->map(function ($eventGroups) {
            // Clone the event groups
            $clonedGroups = $eventGroups->map(function ($group) {
                return collect($group); // Create a clone of each group
            });


            return $clonedGroups->pluck('event_id'); // Return proposed dates
        });

        ///
        $resultsEventNames = $results->map(function ($eventGroups) {
            // Clone the event groups
            $clonedGroups = $eventGroups->map(function ($group) {
                return collect($group); // Create a clone of each group
            });


            return $clonedGroups->pluck('event'); // Return proposed dates
        });

        // Get proposed dates, shifting and popping elements
        $resultsProposedDates = $results->map(function ($eventGroups) {
            // Clone the event groups
            $clonedGroups = $eventGroups->map(function ($group) {
                return collect($group); // Create a clone of each group
            });

            $shifted = $clonedGroups->shift(); // Remove the first element
            $popped = $clonedGroups->pop();     // Remove the last element

            return $clonedGroups->pluck('ncd_next_review')->prepend('')->push(''); // Return proposed dates
        });

        // Get actual dates, shifting and popping elements
        $resultsActualDates = $results->map(function ($eventGroups) {
            // Clone the event groups
            $clonedGroups = $eventGroups->map(function ($group) {
                return collect($group); // Create a clone of each group
            });

            $shifted = $clonedGroups->shift(2); // Remove the first 2 elements


            return $clonedGroups->pluck('ncd_visit_date')->prepend('')->push(''); // Return actual dates
        });

        //
        $resultXY = $resultsEvents->map(function ($item, $index) use ($resultsProposedDates, $resultsActualDates, $resultsEventNames) {

            $proposedDates = $resultsProposedDates[$index];
            $actualDates = $resultsActualDates[$index];

            //dd($proposedDates, $actualDates);

            $dateDiffs = $proposedDates->zip($actualDates)->map(function ($pair) {
                [$proposedDates, $actualDates] = $pair;
                if ($actualDates && $proposedDates) {
                    $carbonDate1 = Carbon::parse($proposedDates);
                    $carbonDate2 = Carbon::parse($actualDates);
                    return $carbonDate1->diffInDays($carbonDate2);
                } else {
                    return null; // Handle empty or invalid dates
                }
            });


            $dateDiffHumans = $proposedDates->zip($actualDates)->map(function ($pair) {
                [$proposedDates, $actualDates] = $pair;
                if ($actualDates && $proposedDates) {
                    $carbonDate1 = Carbon::parse($proposedDates);
                    $carbonDate2 = Carbon::parse($actualDates);
                    return $carbonDate1->diffForHumans($carbonDate2);
                } else {
                    return null; // Handle empty or invalid dates
                }
            });

            $today =  Carbon::today();

            $dateDiffFromNow =  $actualDates->map(function ($date) use ($today) {
                if ($date) {
                    $carbonDate = Carbon::parse($date);
                    return $carbonDate->diffForHumans($today);
                } else {
                    return "Invalid date"; // Handle empty or invalid dates
                }
            });


            $status = $proposedDates->zip($actualDates)->map(function ($pair) {
                [$proposedDates, $actualDates] = $pair;
                if ($actualDates && $proposedDates) {
                    $carbonDate1 = Carbon::parse($proposedDates);
                    $carbonDate2 = Carbon::parse($actualDates);

                    if ($actualDates > $proposedDates) {
                        return 'Late';
                    } else if ($actualDates == $proposedDates) {
                        return 'On Time';
                    } else if ($actualDates < $proposedDates) {
                        return 'Early';
                    }
                } else {
                    return '-'; // Handle empty or invalid dates
                }
            });


            //dd($proposedDates,  $actualDates);
            $statusDistribution = $status->filter()->countBy()->toArray();

            return [
                'event_id' => $item->toArray(),
                'event' => $resultsEventNames[$index]->toArray(),
                'proposed_dates' => $resultsProposedDates[$index]->toArray(),
                'actual_dates' => $resultsActualDates[$index]->toArray(),
                'days_difference' => $dateDiffs->toArray(),
                'human_readable' => $dateDiffHumans->toArray(),
                'diff_from_today' => $dateDiffFromNow->toArray(),
                'status' => $status->toArray(),
                'status_distribution' => $statusDistribution
            ];
        });



        //dd($resultXY['CHK0001']);

        $statusDistribution = $this->getStatusDistribution($resultXY->toArray());
        //$averageDaysDifference = $this->getAverageDaysDifference($results);
        $upcomingAppointments = $this->getUpcomingAppointments($results->toArray());
        $lateAppointments = $this->getLateAppointments($resultXY->toArray());
       // $eventAnalysis = $this->getEventSpecificAnalysis($resultXY->toArray());
        $trends = $this->getRecordSpecificTrends($resultXY->toArray());

        return Inertia::render(
            'Customized/NCD/ReviewDatesAllSites',
            [
                'project' => $project,
                'data' => $resultXY,
                'dataCounts' => [],
                'latestData' => [],
                'statusDistribution' => $statusDistribution,
                'averageDaysDifference' => [],
                'upcomingAppointments' => $upcomingAppointments,
                'lateAppointments' => $lateAppointments,
                //'eventAnalysis' => $eventAnalysis,
                'trends' => $trends,
                'defaulters' => []
            ]
        );
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

            // dd($recordData['status']);
            $arr[] = $recordData['status'];
        }

        // dd();

        return collect($arr)->flatten(1)->countBy();
    }


    /// get Upcoming visits

    private function getUpcomingAppointments(array $results)
    {
        $upcomingAppointments = [];

        foreach ($results as $record => $recordData) {

            $lastVisit[$record] = collect($recordData)->last();

            if (
                $lastVisit[$record]['ncd_next_review'] != "" &&
                Carbon::parse($lastVisit[$record]['ncd_next_review'])->greaterThan(Carbon::today())
            ) {
                //dd($entry['proposed_appointment_date']);
                $daysDifferenceNow = Carbon::parse($lastVisit[$record]['ncd_next_review'])->diffForHumans();
                $upcomingAppointments[$record][$lastVisit[$record]['event']] = collect($recordData)->last();
                $upcomingAppointments[$record][$lastVisit[$record]['event']]['days_difference'] =  $daysDifferenceNow;
            }
        }

        return $upcomingAppointments;
    }
    ///late appointment analysis
    private function getLateAppointments(array $results)
    {
   
        foreach ($results as $record => $recordData) {
            // Loop through the original array
            foreach ($recordData['status'] as $index => $status) {
                if ($status === 'Late') {
                    // Add to the new arrays
                    $transformedArray[$record]['event_id'][$index] = $recordData['event_id'][$index];
                    $transformedArray[$record]['event'][$index] = $recordData['event'][$index];
                    $transformedArray[$record]['proposed_dates'][$index] = $recordData['proposed_dates'][$index];
                    $transformedArray[$record]['actual_dates'][$index] = $recordData['actual_dates'][$index];
                    $transformedArray[$record]['days_difference'][$index] = $recordData['days_difference'][$index];
                    $transformedArray[$record]['human_readable'][$index] = $recordData['human_readable'][$index];
                    $transformedArray[$record]['diff_from_today'][$index] = $recordData['diff_from_today'][$index];
                    $transformedArray[$record]['status'][$index] = $status;
                }
            }
        }
        // Reindex the arrays to maintain the desired structure
        foreach ($transformedArray as &$array) {
            $array = array_values($array); // Reindex the array
        }

        //dd($transformedArray);
        // Output the transformed array
        return $transformedArray;
    }

    /// event specifi
    /// record trenda

    private function getRecordSpecificTrends(array $results)
    {
        $trends = [];

        foreach ($results as $record => $recordData) {
            $trends[$record] = [];

            $trends[$record] = [
                'status' => $recordData['status'],
                'date' => $recordData['proposed_dates'],
            ];

        }

        return $trends;
    }
}
