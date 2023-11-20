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
 */
class DateRange implements DateRangeList, JsonSerializable
{
    /**
     * The start of the range
     */
    public readonly JustDate $start;

    /**
     * The end of the range
     */
    public readonly JustDate $end;

    /**
     * The length of the range in days, measuring from the middle of $this->start to the middle of $this->end
     * So if $start and $end are the same date (shortest possible DateRange), $inner_length will be zero
     * @var non-negative-int
     */
    public readonly int $inner_length;

    /**
     * The length of the range in days, measuring from the start of $this->start to the end of $this->end
     * So if $start and $end are the same date (shortest possible DateRange), $outer_length will be one
     * @var positive-int
     */
    public readonly int $outer_length;

    /**
     * Create a new DateRange object from start and end dates
     *
     * @param JustDate $start Start of range
     * @param JustDate $end End of range
     * @throws InvalidArgumentException If end is before start
     */
    public static function make(JustDate $start, JustDate $end): DateRange
    {
        return new DateRange($start, $end);
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
     * Create a new DateRange object by specifying the start date and the inner length of the range
     *
     * @param JustDate $start Start of range
     * @param non-negative-int $inner_length The desired inner length of the range
     * @throws InvalidArgumentException If inner_length is less than zero
     * @return DateRange The DateRange object
     */
    public static function fromStartAndInnerLength(JustDate $start, int $inner_length): DateRange
    {
        return new DateRange($start, $start->addDays($inner_length));
    }

    /**
     * Create a new DateRange object by specifying the start date and the outer length of the range
     *
     * @param JustDate $start Start of range
     * @param positive-int $outer_length The desired outer length of the range
     * @throws InvalidArgumentException If inner_length is less than one
     * @return DateRange The DateRange object
     */
    public static function fromStartAndOuterLength(JustDate $start, int $outer_length): DateRange
    {
        return new DateRange($start, $start->addDays($outer_length - 1));
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
     * DateRange constructor
     */
    protected function __construct(JustDate $start, JustDate $end)
    {
        if ($start->isAfter($end)) {
            throw new InvalidArgumentException("Start date cannot be after end date");
        }
        $this->start = clone $start;
        $this->end = clone $end;
        $inner_length = JustDate::difference($this->start, $this->end);
        assert($inner_length >= 0); // It must be because $this->start comes before $this->end
        $this->inner_length = $inner_length;
        $this->outer_length = $inner_length + 1;
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
     *
     * @return array{start: string, end: string}
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
     * @param bool $backwards If true the dates will be returned in reverse order (default false).
     * @return Generator<int, JustDate>
     */
    public function each(bool $backwards = false): Generator
    {
        $direction = $backwards ? -1 : 1;
        $first = $backwards ? $this->end : $this->start;
        for ($i = 0; $i < $this->outer_length; $i++) {
            yield $first->addDays($i * $direction);
        }
    }

    /**
     * Get a generator which yields each date in the range (including start but not end) as a JustDate object
     *
     * @param bool $backwards If true the dates will be returned in reverse order, starting with the end date, up to but not including the start date (default false).
     * @return Generator<int, JustDate>
     */
    public function eachExceptLast(bool $backwards = false): Generator
    {
        $direction = $backwards ? -1 : 1;
        $first = $backwards ? $this->end : $this->start;
        for ($i = 0; $i < $this->inner_length; $i++) {
            yield $first->addDays($i * $direction);
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
     * @template T
     * @param callable(JustDate): T $value_fn Callback used to determine how to delimit the subranges
     *     Each subrange will contain dates for which the callback returns
     *     the same value.
     * @param bool $backwards If true the subranges will be returned in reverse order (default false).
     * @return Generator<int, array{range: DateRange, value: T}>
     */
    public function eachSubRange(callable $value_fn, bool $backwards = false): Generator
    {
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

    /**
     * @internal
     * @return DateRange[]
     */
    public function getRanges(): array
    {
        return [$this];
    }
}
