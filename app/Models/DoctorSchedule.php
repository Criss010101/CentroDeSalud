<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['staff_profile_id', 'day_of_week', 'start_time', 'end_time'])]
class DoctorSchedule extends Model
{
    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    /**
     * @return list<string>
     */
    public function availableTimesFor(CarbonInterface $date): array
    {
        $start = Carbon::parse($date->format('Y-m-d').' '.$this->start_time);
        $end = Carbon::parse($date->format('Y-m-d').' '.$this->end_time);
        $bookedTimes = $this->staffProfile
            ->appointments()
            ->whereDate('appointment_date', $date->format('Y-m-d'))
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('start_time')
            ->map(fn (string $time): string => substr($time, 0, 5))
            ->all();
        $availableTimes = [];

        while ($start->copy()->addMinutes(30)->lessThanOrEqualTo($end)) {
            $time = $start->format('H:i');

            if (! in_array($time, $bookedTimes, true) && $start->isFuture()) {
                $availableTimes[] = $time;
            }

            $start->addMinutes(30);
        }

        return $availableTimes;
    }
}
