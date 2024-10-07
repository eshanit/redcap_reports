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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class AppointmentsReviewControllerFancy extends Controller
{
    public function __invoke(string $id)
    {
        // Fetch the project
        $project = Project::select('project_id', 'app_title')->findOrFail($id);

        $results = LazyCollection::make(function () use ($id) {
            // Use lazy collection to iterate through data
            foreach (
                ProjectData::where('project_id', $id)
                    ->whereIn('field_name', ['ncd_visit_date', 'ncd_next_review', 'ncd_tel_pat', 'ncd_tel_kin', 'ncd_health_facility'])
                    ->cursor() as $data
            ) {
                yield $data;
            }
        })->groupBy('record')->map(function ($group) {
            return $group->groupBy('event_id')->map(function ($eventGroup) {
                return [
                    'event_id' => $eventGroup->pluck('event_id')->first(),
                    'event' => $eventGroup->pluck('event')->first(),
                    'ncd_health_facility' => $eventGroup->where('field_name', 'ncd_health_facility')->pluck('value')->first(),
                    'ncd_visit_date' => $eventGroup->where('field_name', 'ncd_visit_date')->pluck('value')->first(),
                    'ncd_next_review' => $eventGroup->where('field_name', 'ncd_next_review')->pluck('value')->first(),
                    'ncd_tel_pat' => $eventGroup->where('field_name', 'ncd_tel_pat')->pluck('value')->first(),
                    'ncd_tel_kin' => $eventGroup->where('field_name', 'ncd_tel_kin')->pluck('value')->first(),
                ];
            });
        });



        // Get proposed dates and actual dates using lazy collections
        $resultsProposedDates = $this->getProposedDates($results);
        $resultsActualDates = $this->getActualDates($results);

        $resultXY = collect();

        // Usage of the generator
        $resultsGenerator = $this->processResults($resultsProposedDates, $resultsActualDates, $results);

        // dd($resultsGenerator);

        $chunkSize = 100; // Define the chunk size
        $tempResults = []; // Temporary array to hold results

        foreach ($resultsGenerator as $processedResult) {
            $tempResults[] = $processedResult; // Collect results in the temporary array

            // Check if we've reached the chunk size
            if (count($tempResults) === $chunkSize) {
                // Push the chunk of results to $resultXY
                $resultXY->push(...$tempResults);
                $tempResults = []; // Reset the temporary array
            }
        }

        // Push any remaining results that didn't fill a complete chunk
        if (!empty($tempResults)) {
            $resultXY->push(...$tempResults);
        }

        dd($resultXY);
        // Now you can use $resultXY as a single consolidated collection
        $statusDistribution = $this->getStatusDistribution($resultXY->toArray());
        $upcomingAppointments = $this->getUpcomingAppointments($results->toArray());
        $lateAppointments = $this->getLateAppointments($resultXY->toArray());
        $trends = $this->getRecordSpecificTrends($resultXY->toArray());
        $latestVisits = $this->getLatestVisit($resultXY->toArray());
        $defaulters = $this->getDefaultedRecords($resultXY->toArray());

        return Inertia::render(
            'Customized/NCD/ReviewDatesAllSites',
            [
                'project' => $project,
                'data' => $resultXY,
                'dataCounts' => $trends,
                'latestData' => $latestVisits,
                'statusDistribution' => $statusDistribution,
                'upcomingAppointments' => $upcomingAppointments,
                'lateAppointments' => $lateAppointments,
                'trends' => $trends,
                'defaulters' => $defaulters,
            ]
        );
    }

    ///
    public function processResults($resultsProposedDates, $resultsActualDates, $results): \Generator
    {
        foreach ($results as $item) {
            // Find the index in the original collection
            $index = $results->search($item);

            // Get proposed and actual dates
            $proposedDates = $resultsProposedDates->get($index);
            $actualDates = $resultsActualDates->get($index);

            $dateDiffs = $this->calculateDateDiffs($proposedDates, $actualDates);
            $dateDiffHumans = $this->calculateDateDiffHumans($proposedDates, $actualDates);
            $dateDiffFromNow = $this->calculateDiffFromNow($actualDates);

            $status = $this->determineStatus($proposedDates, $actualDates);
            $statusDistribution = $status->filter()->countBy()->toArray();

            $facility = $item->pluck('ncd_health_facility')->first(); // Use first() instead of toArray()
            $patientNum = $item->pluck('ncd_tel_pat')->first() ?? '-'; // Default if no patient number
            $kinNum = $item->pluck('ncd_tel_kin')->first() ?? '-'; // Default if no kin number

            // Yield the processed data
            yield [
                'event_id' => $item->pluck('event_id'),
                'event' => $item->pluck('event'),
                'facility' => $facility,
                'tel_pat' => $patientNum,
                'tel_kin' => $kinNum,
                'proposed_dates' => $proposedDates,
                'actual_dates' => $actualDates,
                'days_difference' => $dateDiffs,
                'human_readable' => $dateDiffHumans,
                'diff_from_today' => $dateDiffFromNow,
                'status' => $status,
                'status_distribution' => $statusDistribution,
            ];
        }
    }

    ///
    private function getProposedDates($results)
    {
        return $results->map(function ($eventGroups) {
            return $eventGroups->map(function ($group) {
                return collect($group);
            })->pluck('ncd_next_review')->prepend('')->push('');
        });
    }

    private function getActualDates($results)
    {
        return $results->map(function ($eventGroups) {
            return $eventGroups->map(function ($group) {
                return collect($group);
            })->pluck('ncd_visit_date')->prepend('')->push('');
        });
    }

    private function calculateDateDiffs($proposedDates, $actualDates)
    {
        return $proposedDates->zip($actualDates)->map(function ($pair) {
            [$proposed, $actual] = $pair;
            if ($actual && $proposed) {
                return Carbon::parse($proposed)->diffInDays(Carbon::parse($actual));
            }
            return null;
        });
    }

    private function calculateDateDiffHumans($proposedDates, $actualDates)
    {
        return $proposedDates->zip($actualDates)->map(function ($pair) {
            [$proposed, $actual] = $pair;
            if ($actual && $proposed) {
                return Carbon::parse($actual)->diffForHumans(Carbon::parse($proposed));
            }
            return null;
        });
    }

    private function calculateDiffFromNow($actualDates)
    {
        $today = Carbon::today();
        return $actualDates->map(function ($date) use ($today) {
            if ($date) {
                return Carbon::parse($date)->diffForHumans($today);
            }
            return "Invalid date";
        });
    }

    private function determineStatus($proposedDates, $actualDates)
    {
        return $proposedDates->zip($actualDates)->map(function ($pair) {
            [$proposed, $actual] = $pair;
            if ($actual && $proposed) {
                if ($actual > $proposed) {
                    return 'Late';
                } elseif ($actual == $proposed) {
                    return 'On Time';
                } elseif ($actual < $proposed) {
                    return 'Early';
                }
            }
            return '-';
        });
    }

    private function getStatusDistribution(array $results)
    {

        dd('hi');
        $arr = [];
        foreach ($results as $recordData) {
            $arr[] = $recordData['status'];
        }
        return collect($arr)->flatten(1)->countBy();
    }

    private function getUpcomingAppointments(array $results)
    {
        $upcomingAppointments = [];

        foreach ($results as $record => $recordData) {
            $lastVisit = collect($recordData)->last();

            if ($lastVisit['ncd_next_review'] && Carbon::parse($lastVisit['ncd_next_review'])->greaterThan(Carbon::today())) {
                $daysDifferenceNow = Carbon::parse($lastVisit['ncd_next_review'])->diffForHumans();
                $upcomingAppointments[$record] = collect($recordData)->last();
                $upcomingAppointments[$record]['status'] = 'Pending';
                $upcomingAppointments[$record]['days_difference'] = $daysDifferenceNow;
            }
        }

        return $upcomingAppointments;
    }

    private function getLatestVisit(array $results)
    {
        $transformedArray = [];

        foreach ($results as $record => $recordData) {
            $index = count($recordData['event_id']) - 2;
            if (count($recordData['event_id']) >= 2) {
                $transformedArray[$record] = [
                    'event_id' => $recordData['event_id'][$index],
                    'event' => $recordData['event'][$index],
                    'facility' => $recordData['facility'],
                    'proposed_dates' => $recordData['proposed_dates'][$index],
                    'actual_dates' => $recordData['actual_dates'][$index],
                    'days_difference' => $recordData['days_difference'][$index],
                    'human_readable' => $recordData['human_readable'][$index],
                    'diff_from_today' => $recordData['diff_from_today'][$index],
                    'status' => $recordData['status'][$index],
                    'tel_pat' => $recordData['tel_pat'],
                    'tel_kin' => $recordData['tel_kin'],
                ];
            }
        }

        return $transformedArray;
    }

    private function getLateAppointments(array $results)
    {
        $transformedArray = [];

        foreach ($results as $record => $recordData) {
            foreach ($recordData['status'] as $index => $status) {
                if ($status === 'Late') {
                    // Extract relevant data outside the loop
                    $event_id = $recordData['event_id'][$index];
                    $event = $recordData['event'][$index];
                    $proposed_date = $recordData['proposed_dates'][$index];
                    $actual_date = $recordData['actual_dates'][$index];
                    $days_difference = $recordData['days_difference'][$index];
                    $human_readable = $recordData['human_readable'][$index];
                    $diff_from_today = $recordData['diff_from_today'][$index];

                    // Create indexed arrays directly
                    $transformedArray[$record][] = [
                        'event_id' => $event_id,
                        'event' => $event,
                        'proposed_date' => $proposed_date,
                        'actual_date' => $actual_date,
                        'days_difference' => $days_difference,
                        'human_readable' => $human_readable,
                        'diff_from_today' => $diff_from_today,
                        'status' => $status,
                    ];
                }
            }
        }

        return $transformedArray;
    }


    private function getRecordSpecificTrends(array $results)
    {
        $trends = [];

        foreach ($results as $record => $recordData) {
            $counts = array_count_values($recordData['status']);
            $trends[$record] = [
                'Late Visits' => $counts['Late'] ?? 0,
                'Early Visits' => $counts['Early'] ?? 0,
                'On Time Visits' => $counts['On Time'] ?? 0,
                'No Data' => $counts['-'] ?? 0,
                'status' => $recordData['status'] ?? '-',
                'date' => $recordData['proposed_dates'] ?? '-',
            ];
        }

        return $trends;
    }

    private function getDefaultedRecords(array $results)
    {
        $latestVisitsPerRecords = $this->getLatestVisit($results);
        $defaults = [];

        foreach ($latestVisitsPerRecords as $record => $recordData) {
            $nextReviewDate = Carbon::parse($recordData['proposed_dates']);
            $daysDifferenceNow = $nextReviewDate->diffForHumans();
            $daysDiffNow = $nextReviewDate->diffInDays();

            if ($nextReviewDate->isPast()) {
                $defaults[$record] = [
                    'last_event' => $recordData['event'],
                    'facility' => $recordData['facility'],
                    'proposed_appointment_date' => $nextReviewDate->toDateString(),
                    'actual_dates' => 'No Show',
                    'statusDefault' => $daysDiffNow <= 183 ? 'Missed Appointment' : 'Defaulted',
                    'days_difference' => $daysDifferenceNow,
                    'tel_pat' => $recordData['tel_pat'] ?? '-',
                    'tel_kin' => $recordData['tel_kin'] ?? '-',
                ];
            }
        }

        return $defaults;
    }
}
