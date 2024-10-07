<?php

namespace App\Http\Controllers\Customized\NCD;

ini_set('max_execution_time', -1);

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use Inertia\Inertia;

class Hb1acControllerCopy extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $id)
    {
        //
        $project = Project::select('project_id', 'app_title')->findOrFail($id);

        $results = ProjectData::where('project_id', $id)
            ->whereIn('field_name', [
                'ncd_hba1c',
                'ncd_health_facility',
                'ncd_age',
                'ncd_gender',
                'ncd_visit_date'
            ])
            ->addSelect([
                'event' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data.event_id')
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group) {
                return $group->groupBy('event_id')->map(function ($eventGroup) {
                    $records =  [
                        'event_id' => $eventGroup->pluck('event_id')->first(),
                        'event' => $eventGroup->pluck('event')->first(),
                        'ncd_health_facility' => $eventGroup->where('field_name', 'ncd_health_facility')->pluck('value')->first(),
                        'ncd_age' => $eventGroup->where('field_name', 'ncd_age')->pluck('value')->first(),
                        'ncd_gender' => $eventGroup->where('field_name', 'ncd_gender')->pluck('value')->first(),
                        'ncd_hb1ac' => $eventGroup->where('field_name', 'ncd_hba1c')->pluck('value')->first(),
                        'ncd_visit_date' => $eventGroup->where('field_name', 'ncd_visit_date')->pluck('value')->first()
                    ];
                    return $records;
                });
            });

        $finalHba1cData = $this->hba1cData($results->toArray());
        $statisticsHba1cData = $this->statisticsHb1ac($results->toArray());
        $demographicHba1cData = $this->demographicsHb1ac($results->toArray());

        //  dd($meanHba1cData);

        return Inertia::render(
            'Customized/NCD/HB1AC/Package',
            [
                'project' => $project,
                'results' =>  $finalHba1cData,
                'statisticsResults' => $statisticsHba1cData,
                'demographics' => $demographicHba1cData
            ]
        );
    }

    //restructure array

    private function hba1cData(array $data)
    {
        foreach ($data as $record => $recordData) {
            foreach ($recordData as $index => $eventId) {
                $firstElement[$record] = collect($recordData)->pluck('event_id')->first();
                $gender = $recordData[$firstElement[$record]]['ncd_gender'];

                $transformedArray[$record]['health_facility'] = $recordData[$firstElement[$record]]['ncd_health_facility'];
                $transformedArray[$record]['age'] = $recordData[$firstElement[$record]]['ncd_age'];
                $transformedArray[$record]['gender'] = $gender == 1 ? 'Male' : 'Female';
                $transformedArray[$record]['event'] = $recordData[$index]['event'];
                $transformedArray[$record]['event_id'] = $recordData[$index]['event_id'];
                $transformedArray[$record]['hb1ac'] = $recordData[$index]['ncd_hb1ac'];
                $transformedArray[$record]['visit_date'] = $recordData[$index]['ncd_visit_date'];
                //remove null values
                if ($transformedArray[$record]['hb1ac'] != null) {
                    $finalArray[$record][] = $transformedArray[$record];
                }
            }
        }

        return $finalArray;
    }
    private function statisticsHb1ac(array $data)
    {
        $allResults = $this->hba1cData($data);
    
        $statistics = [];
        $normalStatistics = [];
        $prediabetesStatistics = [];
        $diabetesStatistics = [];
    
        foreach ($allResults as $recordId => $visits) {
            $validHb1acValues = [];
    
            foreach ($visits as $visit) {
                $hb1acValue = $visit['hb1ac'];
    
                // Check if the value is numeric or can be converted to a float
                if (is_numeric($hb1acValue)) {
                    $validHb1acValues[] = (float)$hb1acValue;
                } elseif (strpos($hb1acValue, '%') !== false) {
                    $hb1acValueWithoutPercent = rtrim($hb1acValue, '%');
                    if (is_numeric($hb1acValueWithoutPercent)) {
                        $validHb1acValues[] = (float)$hb1acValueWithoutPercent;
                    }
                } elseif (str_contains($hb1acValue, ',')) {
                    $hb1acValueWithoutComma = str_replace(',', '.', $hb1acValue);
                    if (is_numeric($hb1acValueWithoutComma)) {
                        $validHb1acValues[] = (float)$hb1acValueWithoutComma;
                    }
                }
            }
    
            // Calculate statistics only if there are valid values
            if (!empty($validHb1acValues)) {
                $mean = array_sum($validHb1acValues) / count($validHb1acValues);
                $max = max($validHb1acValues);
                $min = min($validHb1acValues);
                $count = count($validHb1acValues);
    
                // Calculate standard deviation
                $variance = array_reduce($validHb1acValues, function ($carry, $item) use ($mean) {
                    return $carry + pow($item - $mean, 2);
                }, 0) / $count;
    
                $stdDev = sqrt($variance);
    
                $statistics[$recordId] = [
                    'mean' => number_format($mean, 2),
                    'max' => $max,
                    'min' => $min,
                    'count' => $count,
                    'standard_deviation' => number_format($stdDev, 4),
                ];
    
                // Distribute statistics into categories based on mean HbA1c
                if ($mean <= 5.6) {
                    $normalStatistics[$recordId] = $statistics[$recordId];
                } elseif ($mean <= 6.4) {
                    $prediabetesStatistics[$recordId] = $statistics[$recordId];
                } else {
                    $diabetesStatistics[$recordId] = $statistics[$recordId];
                }
            } else {
                // If no valid values, set default values
                $statistics[$recordId] = [
                    'mean' => 0,
                    'max' => 0,
                    'min' => 0,
                    'count' => 0,
                    'standard_deviation' => 0,
                ];
            }
        }
    
        // Return structured output
        return [
            'all' => $statistics,
            'normal' => $normalStatistics ?? [],
            'prediabetes' => $prediabetesStatistics ?? [],
            'diabetes' => $diabetesStatistics ?? [],
        ];
    }

    private function demographicsHb1ac(array $data) 
    {
        $allResults = $this->hba1cData($data);
    
        $genderStatistics = [];
        $healthFacilityStatistics = [];
        $ageGroupStatistics = [];
    
        // Define age groups
        $ageGroups = [
            '0-20' => [],
            '21-40' => [],
            '41-60' => [],
            '60+' => [],
        ];
    
        foreach ($allResults as $records) {
            foreach ($records as $visit) {
                // Convert hb1ac to float
                $hb1ac = (float)str_replace(',', '.', $visit['hb1ac']);
                $gender = $visit['gender'] ?? 'Unknown'; // Handle unknown gender
                $healthFacility = $visit['health_facility'] ?? 'Unknown'; // Handle unknown facility
                $age = (int)($visit['age'] ?? 0); // Default to 0 if age is not set
    
                // Update gender statistics
                if (!isset($genderStatistics[$gender])) {
                    $genderStatistics[$gender] = [
                        'mean' => 0,
                        'max' => $hb1ac,
                        'min' => $hb1ac,
                        'count' => 0,
                        'total' => 0,
                    ];
                }
                $genderStatistics[$gender]['count']++;
                $genderStatistics[$gender]['total'] += $hb1ac;
                $genderStatistics[$gender]['max'] = max($genderStatistics[$gender]['max'], $hb1ac);
                $genderStatistics[$gender]['min'] = min($genderStatistics[$gender]['min'], $hb1ac);
    
                // Update health facility statistics
                if (!isset($healthFacilityStatistics[$healthFacility])) {
                    $healthFacilityStatistics[$healthFacility] = [
                        'mean' => 0,
                        'max' => $hb1ac,
                        'min' => $hb1ac,
                        'count' => 0,
                        'total' => 0,
                    ];
                }
                
                $healthFacilityStatistics[$healthFacility]['count']++;
                $healthFacilityStatistics[$healthFacility]['total'] += $hb1ac;
                $healthFacilityStatistics[$healthFacility]['max'] = max($healthFacilityStatistics[$healthFacility]['max'], $hb1ac);
                $healthFacilityStatistics[$healthFacility]['min'] = min($healthFacilityStatistics[$healthFacility]['min'], $hb1ac);
    
                // Update age group statistics
                if ($age <= 20) {
                    $ageGroup = '0-20';
                } elseif ($age <= 40) {
                    $ageGroup = '21-40';
                } elseif ($age <= 60) {
                    $ageGroup = '41-60';
                } else {
                    $ageGroup = '60+';
                }
    
                if (!isset($ageGroupStatistics[$ageGroup])) {
                    $ageGroupStatistics[$ageGroup] = [
                        'mean' => 0,
                        'max' => $hb1ac,
                        'min' => $hb1ac,
                        'count' => 0,
                        'total' => 0,
                    ];
                }
                $ageGroupStatistics[$ageGroup]['count']++;
                $ageGroupStatistics[$ageGroup]['total'] += $hb1ac;
                $ageGroupStatistics[$ageGroup]['max'] = max($ageGroupStatistics[$ageGroup]['max'], $hb1ac);
                $ageGroupStatistics[$ageGroup]['min'] = min($ageGroupStatistics[$ageGroup]['min'], $hb1ac);
            }
        }
    
        // Calculate mean for each category
        foreach ($genderStatistics as $key => $stats) {
            if ($stats['count'] > 0) {
                $genderStatistics[$key]['mean'] = number_format(($stats['total'] / $stats['count']), 2);
            }
        }
    
        foreach ($healthFacilityStatistics as $key => $stats) {
            if ($stats['count'] > 0) {
                $healthFacilityStatistics[$key]['mean'] = number_format(($stats['total'] / $stats['count']), 2);
            }
        }
    
        foreach ($ageGroupStatistics as $key => $stats) {
            if ($stats['count'] > 0) {
                $ageGroupStatistics[$key]['mean'] = number_format(($stats['total'] / $stats['count']), 2);
            }
        }
    
        // Initialize categorized statistics
        $genderNormalStatistics = $genderPreStatistics = $genderDiabetesStatistics = [];
        $ageGroupNormalStatistics = $agePreGroupStatistics = $ageGroupDiabetesStatistics = [];
        $healthFacilityNormalStatistics = $healthFacilityPreStatistics = $healthFacilityDiabetesStatistics = [];
    
        // Distribute statistics into categories
        foreach ($genderStatistics as $key => $stats) {
            if ($stats['mean'] <= 5.6) {
                $genderNormalStatistics[$key] = $stats;
            } elseif ($stats['mean'] <= 6.4) {
                $genderPreStatistics[$key] = $stats;
            } else {
                $genderDiabetesStatistics[$key] = $stats;
            }
        }
    
        foreach ($ageGroupStatistics as $key => $stats) {
            if ($stats['mean'] <= 5.6) {
                $ageGroupNormalStatistics[$key] = $stats;
            } elseif ($stats['mean'] <= 6.4) {
                $agePreGroupStatistics[$key] = $stats;
            } else {
                $ageGroupDiabetesStatistics[$key] = $stats;
            }
        }
    
        foreach ($healthFacilityStatistics as $key => $stats) {
            if ($stats['mean'] <= 5.6) {
                $healthFacilityNormalStatistics[$key] = $stats;
            } elseif ($stats['mean'] <= 6.4) {
                $healthFacilityPreStatistics[$key] = $stats;
            } else {
                $healthFacilityDiabetesStatistics[$key] = $stats;
            }
        }
    
        // Return structured output
        return [
            'all' => [
                'genderStatistics' => $genderStatistics,
                'ageStatistics' => $ageGroupStatistics,
                'healthFacility' => $healthFacilityStatistics,
            ],
            'normal' => [
                'genderStatistics' => $genderNormalStatistics,
                'ageStatistics' => $ageGroupNormalStatistics,
                'healthFacility' => $healthFacilityNormalStatistics,
            ],
            'prediabetes' => [
                'genderStatistics' => $genderPreStatistics,
                'ageStatistics' => $agePreGroupStatistics,
                'healthFacility' => $healthFacilityPreStatistics,
            ],
            'diabetes' => [
                'genderStatistics' => $genderDiabetesStatistics,
                'ageStatistics' => $ageGroupDiabetesStatistics,
                'healthDiabetes' => $healthFacilityDiabetesStatistics,
            ],
        ];
    }
}
