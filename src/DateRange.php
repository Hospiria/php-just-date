<?php

namespace MadisonSolutions\JustDate;

use InvalidArgumentException;
use JsonSerializable;

class DateRange implements JsonSerializable
{
    /**
     * @var MadisonSolutions\JustDate\JustDate
     */
    protected $start;

    /**
     * @var MadisonSolutions\JustDate\JustDate
     */
    protected $end;

    /**
     * Create a new DateRange object from start and end date as Y-m-d formatted strings
     *
     * @param string $start Start of range, in Y-m-d format
     * @param string $end End of range, in Y-m-d format
     * @throws InvalidArgumentException If start or end are invalid Y-m-d strings, or if end is before start
     * @return MadisonSolutions\JustDate\DateRange The DateRange object
     */
    public static function fromYmd(string $start, string $end) : DateRange
    {
        return new DateRange(JustDate::fromYmd($start), JustDate::fromYmd($end));
    }

    /**
     * Create a new DateRange object which is the intersection of $r1 and $r2
     *
     * If $r1 and $r2 have no intersection and are totally separate, then this function returns null
     *
     * @param MadisonSolutions\JustDate\DateRange $r1 The first range
     * @param MadisonSolutions\JustDate\DateRange $r2 The second range
     * @return ?MadisonSolutions\JustDate\DateRange The intersection DateRange object or null
     */
    public static function intersection(DateRange $r1, DateRange $r2) : ?DateRange
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
     * @param MadisonSolutions\JustDate\JustDate $start Start of range
     * @param MadisonSolutions\JustDate\JustDate $end End of range
     * @throws InvalidArgumentException If end is before start
     */
    public function __construct(JustDate $start, JustDate $end)
    {
        if ($start->isAfter($end)) {
            throw new \InvalidArgumentException("Start date cannot be after end date");
        }
        $this->start = clone $start;
        $this->end = clone $end;
    }

    /**
     * Getters
     *
     * start - The start of the range
     * end - The end of the range
     * span - The number of nights between start and end (if start and end are same day, span is 0)
     * num_nights - Alias for span
     * num_days - The number of days in the range, including start end end (if start and end are same day, num_days is 1)
     */
    public function __get($name)
    {
        switch ($name) {
            case 'start':
                return $this->start;
            case 'end':
                return $this->end;
            case 'span':
            case 'num_nights':
                return JustDate::spanDays($this->start, $this->end);
            case 'num_days':
                return JustDate::spanDays($this->start, $this->end) + 1;
        }
        return null;
    }

    public function __isset($name)
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
     * Standard string representation is eg '2019-04-21 to 2019-04-25'
     */
    public function __toString()
    {
        return "{$this->start} to {$this->end}";
    }

    /**
     * Json representation is object with 'start' and 'end' properties
     */
    public function jsonSerialize()
    {
        return [
            'start' => (string) $this->start,
            'end' => (string) $this->end,
        ];
    }

    /**
     * Get a generator which yields each date in the range (inclusive of end points) as a JustDate object
     */
    public function each()
    {
        for ($day = $this->start; $day->isBeforeOrSameAs($this->end); $day = $day->nextDay()) {
            yield $day;
        }
    }

    /**
     * Get a generator which yields each date in the range (including start but not end) as a JustDate object
     */
    public function eachExceptEnd()
    {
        for ($day = $this->start; $day->isBefore($this->end); $day = $day->nextDay()) {
            yield $day;
        }
    }

    /**
     * Test whether a particular date lies within this range
     *
     * @param MadisonSolutions\JustDate\JustDate $date The date to test
     * @return bool True if the date is within this range (including endpoints), false otherwise
     */
    public function includes(JustDate $date) : bool
    {
        return $date->isAfterOrSameAs($this->start) && $date->isBeforeOrSameAs($this->end);
    }

    /**
     * Test whether a particular date range is completely contained within this range
     *
     * @param MadisonSolutions\JustDate\DateRange $range The range to test
     * @return bool True if $range is completely contained within this range, false otherwise
     */
    public function contains(DateRange $range) : bool
    {
        return $this->start->isBeforeOrSameAs($range->start) && $this->end->isAfterOrSameAs($range->end);
    }
}
