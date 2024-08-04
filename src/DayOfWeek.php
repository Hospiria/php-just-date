<?php

namespace MadisonSolutions\JustDate;

/**
 * Enum DayOfWeek
 */
enum DayOfWeek: int
{
    case Sunday = 0;
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;

    /**
     * Is this a weekday (mon - fri)?
     */
    public function isWeekday(): bool
    {
        return match ($this) {
            DayOfWeek::Sunday, DayOfWeek::Saturday => false,
            default => true,
        };
    }

    /**
     * Is this a weekend day (sat or sun)?
     */
    public function isWeekend(): bool
    {
        return match ($this) {
            DayOfWeek::Sunday, DayOfWeek::Saturday => true,
            default => false,
        };
    }

    /**
     * Return the new DayOfWeek after adding $num days
     */
    public function addDays(int $num): DayOfWeek
    {
        return DayOfWeek::from(((($this->value + $num) % 7) + 7) % 7);
    }

    /**
     * Return the new DayOfWeek after subtracting $num days
     */
    public function subDays(int $num): DayOfWeek
    {
        return $this->addDays(-$num);
    }

    /**
     * Return the number of days, counting forward from this DayOfWeek, until the next instance of the specified DayOfWeek
     *
     * Returns zero if the specified DayOfWeek is the same as this one
     *
     * For example DayOfWeek::Sunday->numDaysUntil(DayOfWeek::Monday) is 1
     * DayOfWeek::Monday->numDaysUntil(DayOfWeek::Sunday) is 6
     *
     * @param  DayOfWeek  $to  The target DayOfWeek
     * @return int The number of days until the target DayOfWeek
     */
    public function numDaysUntil(DayOfWeek $to): int
    {
        return (($to->value - $this->value) + 7) % 7;
    }

    /**
     * Return the number of days, counting backwards from this DayOfWeek, until the previous instance of the specified DayOfWeek
     *
     * Returns zero if the specified DayOfWeek is the same as this one
     *
     * For example DayOfWeek::Sunday->numDaysSince(DayOfWeek::Monday) is 6
     * DayOfWeek::Monday->numDaysUntil(DayOfWeek::Sunday) is 1
     *
     * @param  DayOfWeek  $from  The target DayOfWeek
     * @return int The number of days until the target DayOfWeek
     */
    public function numDaysSince(DayOfWeek $from): int
    {
        return $from->numDaysUntil($this);
    }
}
