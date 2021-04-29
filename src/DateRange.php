<?php

namespace MadisonSolutions\JustDate;

use Generator;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class DateRange
 *
 * Class representing a range of dates
 * Ranges that contain a single date are allowed (IE the start and end date are the same)
 * Ranges that contain no dates are impossible
 *
 * @package MadisonSolutions\JustDate
 * @property JustDate $start
 * @property JustDate $end
 * @property int $span
 * @property int $num_nights
 * @property int $num_days
 */
class DateRange implements DateRangeList, JsonSerializable
{
    protected JustDate $start;
    protected JustDate $end;

    /**
     * Create a new DateRange object from start and end date as Y-m-d formatted strings
     *
     * @param string $start Start of range, in Y-m-d format
     * @param string $end End of range, in Y-m-d format
     * @throws InvalidArgumentException If start or end are invalid Y-m-d strings, or if end is before start
     * @return DateRange The DateRange object
     */
    public static function fromYmd(string $start, string $end): DateRange
    {
        return new DateRange(JustDate::fromYmd($start), JustDate::fromYmd($end));
    }

    /**
     * Create a new DateRange object which is the intersection of $r1 and $r2
     *
     * If $r1 and $r2 have no intersection and are totally separate, then this function returns null
     *
     * @param DateRange $r1 The first range
     * @param DateRange $r2 The second range
     * @return ?DateRange The intersection DateRange object or null
     */
    public static function intersection(DateRange $r1, DateRange $r2): ?DateRange
    {
        $start = JustDate::latest($r1->start, $r2->start);
        $end = JustDate::earliest($r1->end, $r2->end);
        if ($start->isAfter($end)) {
            // There's no intersection
            return null;
        }
        return new DateRange($start, $end);
    }

    /**
     * Create a new DateRange object from start and end dates
     *
     * @param JustDate $start Start of range
     * @param JustDate $end End of range
     * @throws InvalidArgumentException If end is before start
     */
    public function __construct(JustDate $start, JustDate $end)
    {
        if ($start->isAfter($end)) {
            throw new InvalidArgumentException("Start date cannot be after end date");
        }
        $this->start = clone $start;
        $this->end = clone $end;
    }

    /**
     * Create a new DateRange objects from start and end dates specified in any order
     *
     * The start date will be whichever of the 2 dates is earliest and the end date
     * whichever of the 2 dates is latest.
     *
     * @param JustDate $a Start or end of range
     * @param JustDate $b Other side of range
     * @return DateRange The DateRange object
     */
    public static function eitherWayRound(JustDate $a, JustDate $b): DateRange
    {
        return new DateRange(JustDate::earliest($a, $b), JustDate::latest($a, $b));
    }

    /**
     * Getters
     *
     * start - The start of the range
     * end - The end of the range
     * num_nights - The number of nights between the start and end of the range (if start and end are same day, num_nights is 0)
     * num_days - The number of days in the range, including start and end (if start and end are same day, num_days is 1)
     *
     * @param $name
     * @return int|JustDate|null
     */
    public function __get($name)
    {
        switch ($name) {
            case 'start':
                return $this->start;
            case 'end':
                return $this->end;
            case 'num_nights':
                return JustDate::numNights($this->start, $this->end);
            case 'num_days':
                return JustDate::numNights($this->start, $this->end) + 1;
        }
        return null;
    }

    public function __isset($name): bool
    {
        switch ($name) {
            case 'start':
            case 'end':
            case 'span':
                return true;
        }
        return false;
    }

    /**
     * Does this range consist of just a single day?
     * IE start date and end date are the same
     *
     * @return bool
     */
    public function isSingleDay(): bool
    {
        return $this->start->isSameAs($this->end);
    }

    /**
     * Standard string representation is eg '2019-04-21 to 2019-04-25'
     */
    public function __toString(): string
    {
        return "{$this->start} to {$this->end}";
    }

    /**
     * Json representation is object with 'start' and 'end' properties
     */
    public function jsonSerialize(): array
    {
        return [
            'start' => (string) $this->start,
            'end' => (string) $this->end,
        ];
    }

    /**
     * Get a generator which yields each date in the range (inclusive of end points) as a JustDate object
     *
     * @return Generator
     */
    public function each(): Generator
    {
        for ($day = $this->start; $day->isBeforeOrSameAs($this->end); $day = $day->nextDay()) {
            yield $day;
        }
    }

    /**
     * Get a generator which yields each date in the range (including start but not end) as a JustDate object
     *
     * @return Generator
     */
    public function eachExceptEnd(): Generator
    {
        for ($day = $this->start; $day->isBefore($this->end); $day = $day->nextDay()) {
            yield $day;
        }
    }

    /**
     * Test whether a particular date lies within this range
     *
     * @param JustDate $date The date to test
     * @return bool True if the date is within this range (including endpoints), false otherwise
     */
    public function includes(JustDate $date): bool
    {
        return $date->isAfterOrSameAs($this->start) && $date->isBeforeOrSameAs($this->end);
    }

    /**
     * Test whether a particular date range is completely contained within this range
     *
     * @param DateRange $range The range to test
     * @return bool True if $range is completely contained within this range, false otherwise
     */
    public function contains(DateRange $range): bool
    {
        return $this->start->isBeforeOrSameAs($range->start) && $this->end->isAfterOrSameAs($range->end);
    }

    /**
     * Get a generator which splits the range into subranges
     *
     * The supplied callback function will be applied to each date in the range,
     * and consecutive dates for which the callback returns equal values will be
     * grouped together into a subrange.
     *
     * This function returns a generator which will yield each of these contiguous
     * subranges in turn, together with the callback value. The yield values will
     * be in the format of an array with 'value' and 'range' keys.
     *
     * @param callable $value_fn Callback used to determine how to delimit the subranges
     *                 Each subrange will contain dates for which the callback returns
     *                 the same value.
     * @param array $opts Array of options. Currently one option is supported, boolean
     *              'backwards' (default false).  If true the subranges will be returned
     *              in reverse order.
     * @return Generator
     */
    public function iterateSubRanges(callable $value_fn, array $opts = []): Generator
    {
        $backwards = (bool) ($opts['backwards'] ?? false);
        $step = $backwards ? -1 : 1;
        $end = $backwards ? $this->start : $this->end;

        $sub_range_start = $backwards ? $this->end : $this->start;
        $sub_range_end = $sub_range_start;
        $sub_range_value = $value_fn($sub_range_start);

        while (! $sub_range_end->isSameAs($end)) {
            $next_date = $sub_range_end->addDays($step);
            $next_value = $value_fn($next_date);
            if ($next_value != $sub_range_value) {
                // Value has changed
                yield [
                    'range' => DateRange::eitherWayRound($sub_range_start, $sub_range_end),
                    'value' => $sub_range_value,
                ];
                // Start a new current range
                $sub_range_start = $next_date;
                $sub_range_value = $next_value;
            }
            $sub_range_end = $next_date;
        }

        // Finish the current range
        yield [
            'range' => DateRange::eitherWayRound($sub_range_start, $sub_range_end),
            'value' => $sub_range_value,
        ];
    }

    public function getRanges(): array
    {
        return [$this];
    }
}
