<?php

namespace MadisonSolutions\JustDate;

/**
 * Enum DayOfWeek
 *
 * @package MadisonSolutions\JustDate
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
}
