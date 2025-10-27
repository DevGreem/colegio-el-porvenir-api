<?php

namespace App\Support;

use Illuminate\Support\Collection;

class ScheduleGenerator
{
    public static function generate(Collection $subjects, int $periodsPerDay = 6): array
    {
        $days = self::days();
        $timeSlots = self::timeSlots($periodsPerDay);

        $schedule = [];
        foreach ($days as $day) {
            $schedule[$day] = [];
        }

        if ($subjects->isEmpty() || empty($timeSlots)) {
            return $schedule;
        }

        $recessConfig = config('schedule.recess', []);
        $recessName = strtolower($recessConfig['subject_name'] ?? 'Recreo');
        $recessTime = $recessConfig['time'] ?? null;

        $recessSubjectId = null;
        $slots = [];
        $subjectPool = [];

        foreach ($subjects as $subject) {
            $subjectId = is_array($subject) ? ($subject['id'] ?? null) : ($subject->id ?? null);
            $weeklyHours = is_array($subject) ? ($subject['weekly_hours'] ?? 0) : ($subject->weekly_hours ?? 0);
            $subjectName = strtolower(is_array($subject) ? ($subject['name'] ?? '') : ($subject->name ?? ''));

            if (!$subjectId || $weeklyHours <= 0) {
                continue;
            }

            if ($subjectName === $recessName) {
                $recessSubjectId ??= (int) $subjectId;
                continue;
            }

            for ($i = 0; $i < $weeklyHours; $i++) {
                $subjectSlotsId = (int) $subjectId;
                $slots[] = $subjectSlotsId;
                $subjectPool[] = $subjectSlotsId;
            }
        }

        shuffle($slots);

        foreach ($days as $day) {
            foreach ($timeSlots as $time) {
                $schedule[$day][] = [
                    'hour' => $time,
                    'subject_id' => ($recessSubjectId && $recessTime === $time)
                        ? $recessSubjectId
                        : null,
                ];
            }
        }

        while (!empty($slots)) {
            $assigned = false;

            foreach ($days as $day) {
                $index = self::nextEmptySlotIndex($schedule[$day]);

                if ($index === null) {
                    continue;
                }

                $schedule[$day][$index]['subject_id'] = array_shift($slots);
                $assigned = true;

                if (empty($slots)) {
                    break;
                }
            }

            if (!$assigned) {
                break;
            }
        }

        if (!empty($subjectPool)) {
            shuffle($subjectPool);
            $poolSize = count($subjectPool);
            $poolIndex = 0;

            foreach ($days as $day) {
                foreach ($schedule[$day] as $index => $entry) {
                    if (!array_key_exists('subject_id', $entry) || is_null($entry['subject_id'])) {
                        $schedule[$day][$index]['subject_id'] = $subjectPool[$poolIndex % $poolSize];
                        $poolIndex++;
                    }
                }
            }
        }

        return $schedule;
    }

    /**
     * @return string[]
     */
    protected static function days(): array
    {
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    }

    /**
     * @return list<string>
     */
    protected static function timeSlots(int $periodsPerDay): array
    {
        $defined = config('schedule.time_slots', []);

        if (is_array($defined) && !empty($defined)) {
            return array_values($defined);
        }

        $startTime = config('schedule.day_start_time', '06:30');
        $periodMinutes = (int) config('schedule.period_minutes', 60);

        $current = \Carbon\Carbon::createFromFormat('H:i', $startTime);
        $slots = [];

        for ($i = 0; $i < $periodsPerDay; $i++) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($periodMinutes);
        }

        return $slots;
    }

    /**
     * @param list<array{hour: string, subject_id: int|null}> $entries
     */
    protected static function nextEmptySlotIndex(array $entries): ?int
    {
        foreach ($entries as $index => $entry) {
            if (!array_key_exists('subject_id', $entry) || is_null($entry['subject_id'])) {
                return $index;
            }
        }

        return null;
    }
}
