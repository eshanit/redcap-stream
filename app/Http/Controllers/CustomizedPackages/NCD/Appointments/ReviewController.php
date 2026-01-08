<?php
namespace App\Http\Controllers\CustomizedPackages\NCD\Appointments;
// ini_set('memory_limit', '512M');
ini_set('max_execution_time', -1);

use App\Models\Project;
use App\Models\ProjectData3;
use App\Models\ProjectEventMetadata;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(string $id)
    {
        $project = Project::select('project_id', 'app_title')->findOrFail($id);
        $results = ProjectData3::where('project_id', $id)
            // ->addSelect([
            //     'event' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data3.event_id')
            // ])
            ->whereIn('field_name', ['ncd_visit_date', 'ncd_next_review', 'ncd_tel_pat', 'ncd_tel_kin', 'ncd_health_facility'])
            ->get()
            ->groupBy('record')
            ->map(function ($group) {
                return $group->groupBy('instance')->map(function ($eventGroup) {
                    return [
                        'event_id' => $eventGroup->pluck('event_id')->first(),
                        //'visit' => $eventGroup->pluck('event')->first(),
                        'event' => $eventGroup->pluck('instance')->first() ?? 1, // using instance as visit
                        'ncd_health_facility' => $eventGroup->where('field_name', 'ncd_health_facility')->pluck('value')->first(),
                        'ncd_visit_date' => $eventGroup->where('field_name', 'ncd_visit_date')->pluck('value')->first(),
                        'ncd_next_review' => $eventGroup->where('field_name', 'ncd_next_review')->pluck('value')->first(),
                        'ncd_tel_pat' => $eventGroup->where('field_name', 'ncd_tel_pat')->pluck('value')->first(),
                        'ncd_tel_kin' => $eventGroup->where('field_name', 'ncd_tel_kin')->pluck('value')->first(),
                    ];
                });
            });

        // Get proposed dates, shifting and popping elements
        $resultsElements = $results->map(function ($eventGroups) {
            // Clone the event groups
            $clonedGroups = $eventGroups->map(function ($group) {
                return collect($group); // Create a clone of each group

            });
            return $clonedGroups->select('event_id', 'event', 'ncd_health_facility', 'ncd_tel_pat', 'ncd_tel_kin'); // Return proposed dates

        });

        //Get date when visit was done
        $resultsCurrentVisitDates = $results->map(function ($eventGroups) {
            // Clone the event groups
            $clonedGroups = $eventGroups->map(function ($group) {
                return collect($group); // Create a clone of each group
            });
      
           // return $clonedGroups;
           // dd($clonedGroups);
           // $shifted = $clonedGroups->shift(); // Remove the first element

            $dates = $clonedGroups->pluck('ncd_visit_date')->prepend('');

            if ($clonedGroups->count() > 2) {
                //$popped = $clonedGroups->pop(); // Remove the last element

                $clonedGroups->pluck('ncd_visit_date')->push('');
            };

            return $clonedGroups->pluck('ncd_visit_date'); // Return proposed dates

        });

        //dd( $resultsCurrentVisitDates['CHK0001'],$resultsCurrentVisitDates['MPH0735'], $resultsCurrentVisitDates['MPH473'] );
        // Get proposed dates, shifting and popping elements

        $resultsProposedDates = $results->map(function ($eventGroups) {
            // Clone the event groups
            $clonedGroups = $eventGroups->map(function ($group) {
                return collect($group); // Create a clone of each group
            });
      
           // return $clonedGroups;
           // dd($clonedGroups);
            //$shifted = $clonedGroups->shift(); // Remove the first element

            $dates = $clonedGroups->pluck('ncd_next_review')->prepend('');

            if ($clonedGroups->count() > 2) {
                $popped = $clonedGroups->pop(); // Remove the last element

                $clonedGroups->pluck('ncd_next_review')->push('');
            };

            return $dates; // Return proposed dates

        });

        $resultsActualDates = $resultsCurrentVisitDates;

        $resultsActualDatesx = $results->map(function ($eventGroups) {
            // Clone the event groups
            $clonedGroups = $eventGroups->map(function ($group) {
                return collect($group); // Create a clone of each group
            });

           // return $clonedGroups;

            $shifted = $clonedGroups->shift(2); // Remove the first 2 elements
            return $clonedGroups->pluck('ncd_visit_date')->prepend('')->push(''); // Return actual dates

        });
//dd( $resultsActualDates['CHK0001'],$resultsActualDates['MPH0735'] );

        $resultXY = $resultsElements->map(function ($item, $index) use ($resultsCurrentVisitDates, $resultsProposedDates, $resultsActualDates) {
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
                    return $carbonDate2->diffForHumans($carbonDate1);
                } else {
                    return null; // Handle empty or invalid dates
                }
            });

            $today = Carbon::today();
            $dateDiffFromNow = $actualDates->map(function ($date) use ($today) {
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


            $statusDistribution = $status->filter()->countBy()->toArray();
            $facility = $item->pluck('ncd_health_facility')->toArray();
            $patient_num = $item->pluck('ncd_tel_pat')->toArray();
            $kin_num = $item->pluck('ncd_tel_kin')->toArray();

            return [
               // 'index' => $index,
                'event_id' => $item->pluck('event_id')->toArray(),
                'event' => $item->pluck('event')->toArray(),
                'facility' => $facility[0],
                'tel_pat' => $patient_num[0] ?? '-',
                'tel_kin' => $kin_num[0] ?? '-',
                'visit_dates' => $resultsCurrentVisitDates[$index]->toArray(),
                'proposed_dates' => $resultsProposedDates[$index]->toArray(),
                'actual_dates' => $resultsActualDates[$index]->toArray(),
                'days_difference' => $dateDiffs->toArray(),
                'human_readable' => $dateDiffHumans->toArray(),
                'diff_from_today' => $dateDiffFromNow->toArray(),
                'status' => $status->toArray(),
                'status_distribution' => $statusDistribution
            ];
        });

        $statusDistribution = $this->getStatusDistribution($resultXY->toArray());
        $upcomingAppointments = $this->getUpcomingAppointments($results->toArray());
        $lateAppointments = $this->getLateAppointments($resultXY->toArray());
        $trends = $this->getRecordSpecificTrends($resultXY->toArray());
        $latestVisits = $this->getLatestVisit($resultXY->toArray());
        $defaulters = $this->getDefaultedRecords($resultXY->toArray());

        return Inertia::render(
            'Customizations/NCD/Appointments/Review',
            [
                'project' => $project,
                'data' => $resultXY,
                'dataCounts' => $trends,
                'latestData' => $latestVisits,
                'statusDistribution' => $statusDistribution,
                'upcomingAppointments' => $upcomingAppointments,
                'lateAppointments' => $lateAppointments,
                'trends' => $trends,
                'defaulters' => $defaulters
            ]
        );
    }


    /**

     * Status distribution

     */

    private function getStatusDistribution(array $results)

    {
        foreach ($results as $recordData) {
            $arr[] = $recordData['status'];
        }
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
                $daysDifferenceNow = Carbon::parse($lastVisit[$record]['ncd_next_review'])->diffForHumans();
                $upcomingAppointments[$record] = collect($recordData)->last();
                $upcomingAppointments[$record]['status'] = 'Pending';
                $upcomingAppointments[$record]['days_difference'] = $daysDifferenceNow;
            }
        }

        return $upcomingAppointments;
    }


    private function getLatestVisit(array $results)
    {
   //dd($results['MPH0735']);

        foreach ($results as $record => $recordData) {
            $index = count($recordData['event_id'])-2;
            if (count($recordData['event_id']) > 2) {

                $transformedArray[$record]['event_id'] = $recordData['event_id'][$index];
                $transformedArray[$record]['event'] = $recordData['event'][$index];
                $transformedArray[$record]['facility'] = $recordData['facility'];
                $transformedArray[$record]['visit_dates'] = $recordData['visit_dates'][$index];
                $transformedArray[$record]['proposed_dates'] = $recordData['proposed_dates'][$index];
                $transformedArray[$record]['actual_dates'] = $recordData['actual_dates'][$index];
                $transformedArray[$record]['days_difference'] = $recordData['days_difference'][$index];
                $transformedArray[$record]['human_readable'] = $recordData['human_readable'][$index];
                $tansformedArray[$record]['diff_from_today'] = $recordData['diff_from_today'][$index];
                $transformedArray[$record]['status'] = $recordData['status'][$index];
                $transformedArray[$record]['tel_pat'] = $recordData['tel_pat'];
                $transformedArray[$record]['tel_kin'] = $recordData['tel_kin'];

            } elseif (count($recordData['event_id']) == 2) {

                $transformedArray[$record]['event_id'] = $recordData['event_id'][1];
                $transformedArray[$record]['event'] = $recordData['event'][1];
                $transformedArray[$record]['facility'] = $recordData['facility'];
                $transformedArray[$record]['visit_dates'] = $recordData['visit_dates'][1];
                $transformedArray[$record]['proposed_dates'] = $recordData['proposed_dates'][1];
                $transformedArray[$record]['actual_dates'] = $recordData['actual_dates'][1];
                $transformedArray[$record]['days_difference'] = $recordData['days_difference'][1];
                $transformedArray[$record]['human_readable'] = $recordData['human_readable'][1];
                $tansformedArray[$record]['diff_from_today'] = $recordData['diff_from_today'][1];
                $transformedArray[$record]['status'] = $recordData['status'][1];
                $transformedArray[$record]['tel_pat'] = $recordData['tel_pat'];
                $transformedArray[$record]['tel_kin'] = $recordData['tel_kin'];

            }
        }

       // dd($tansformedArray['MPH0735']);
        return $transformedArray;
    }

    ///late appointment analysis

    private function getLateAppointments(array $results)
    {
        foreach ($results as $record => $recordData) {
            foreach ($recordData['status'] as $index => $status) {
                if ($status === 'Late') {
                    $transformedArray[$record]['event_id'][$index] = $recordData['event_id'][$index];
                    $transformedArray[$record]['event'][$index] = $recordData['event'][$index];
                    $transformedArray[$record]['visit_dates'] = $recordData['visit_dates'][$index];
                    $transformedArray[$record]['proposed_dates'][$index] = $recordData['proposed_dates'][$index];
                    $transformedArray[$record]['actual_dates'][$index] = $recordData['actual_dates'][$index];
                    $transformedArray[$record]['days_difference'][$index] = $recordData['days_difference'][$index];
                    $transformedArray[$record]['human_readable'][$index] = $recordData['human_readable'][$index];
                    $transformedArray[$record]['diff_from_today'][$index] = $recordData['diff_from_today'][$index];
                    $transformedArray[$record]['status'][$index] = $status;
                }
            }
        }

        foreach ($transformedArray as &$array) {
            $array = array_values($array); // Reindex the array
        }

        return $transformedArray;
    }


    private function getRecordSpecificTrends(array $results)
    {
        $trends = [];
        foreach ($results as $record => $recordData) {
            $trends[$record] = [];
            $counts = array_count_values($recordData['status']);
            // dd($counts);
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

    // defaulters

    private function getDefaultedRecords(array $results)
    {
        $latestVisitsPerRecords = $this->getLatestVisit($results);
        foreach ($latestVisitsPerRecords as $record => $recordData) {
            $visitDate =  Carbon::parse($recordData['visit_dates']);
            $nextReviewDate = Carbon::parse($recordData['proposed_dates']);
            $daysDifferenceNow = $nextReviewDate->diffForHumans();
            $daysDiffNow = $nextReviewDate->diffInDays();
            if ($nextReviewDate->isPast()) {
                $defaults[$record]['last_event'] = $recordData['event'];
                $defaults[$record]['facility'] = $recordData['facility'];
                $defaults[$record]['visit_dates'] = $recordData['visit_dates'];
                $defaults[$record]['proposed_appointment_date'] = $nextReviewDate->toDateString();
                $defaults[$record]['actual_dates'] = 'No Show';
                $defaults[$record]['statusDefault'] = $daysDiffNow <= 183 ? 'Missed Appointment' : 'Defaulted';
                $defaults[$record]['days_difference'] = $daysDifferenceNow;
                $defaults[$record]['tel_pat'] = $recordData['tel_pat'] ?? '-';
                $defaults[$record]['tel_kin'] = $recordData['tel_kin'] ?? '-';
            }
        }

        return $defaults;
    }
}
