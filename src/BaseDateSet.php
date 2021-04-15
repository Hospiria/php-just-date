<?php

namespace MadisonSolutions\JustDate;

use Generator;

/**
 * Class BaseDateSet
 *
 * Base class for DateSet and MutableDateSet
 *
 * @package MadisonSolutions\JustDate
 */
abstract class BaseDateSet implements DateRangeList
{
    /**
     * @var DateRange[]
     */
    protected $ranges;

    /**
     * Utility function that takes any number of JustDate objects and turns them into a normalized list of DateRanges
     *
     * Any consecutive dates in the input will be merged into a single range
     * Any repeated dates will be merged together
     * The resulting list of ranges will be sorted and disjoint
     *
     * @param JustDate ...$dates The input dates
     * @return DateRange[] Resulting normalized list of ranges
     */
    public static function sortedRangesFromSingleDates(JustDate ...$dates) : array
    {
        $ranges = [];
        $num = count($dates);
        if ($num == 0) {
            return $ranges;
        }
        usort($dates, function ($a, $b) {
            return $a->timestamp - $b->timestamp;
        });
        $curr_start = $curr_end = $dates[0];
        for ($i = 1; $i < $num; $i++) {
            if ($dates[$i]->isAfter($curr_end->nextDay())) {
                // there's a gap before the next date
                $ranges[] = new DateRange($curr_start, $curr_end);
                $curr_start = $curr_end = $dates[$i];
            } else {
                $curr_end = $dates[$i];
            }
        }
        $ranges[] = new DateRange($curr_start, $curr_end);
        return $ranges;
    }

    /**
     * Utility function for subtracting a range from an array of ranges
     *
     * Used internally by the DateSet and MutableDateSet classes
     * The supplied array must already be normalized (which is why this function is protected)
     *
     * @param array $ranges Original sorted, disjoint list of ranges
     * @param DateRange $cut Range to be subtracted from each of the ranges
     * @return array Resulting normalized list of ranges after subtracting $cut
     */
    protected static function subtractRangeFromSortedRanges(array $ranges, DateRange $cut)
    {
        $num = count($ranges);
        if ($num == 0) {
            return [];
        }

        $i = 0;
        $new_ranges = [];

        // Start looping over the existing ranges
        // Any which are completely before $cut will go into the new set unchanged
        while ($i < $num && $cut->start->isAfter($ranges[$i]->end)) {
            $new_ranges[] = $ranges[$i];
            $i++;
        }

        // We've now gone past all the ranges that were completely before $cut
        // Continue looping, but now considering ranges that are intersecting $cut in some way
        while ($i < $num && $cut->end->isAfterOrSameAs($ranges[$i]->start)) {
            $existing = $ranges[$i];
            if ($cut->start->isBeforeOrSameAs($existing->start)) {
                if ($cut->end->isAfterOrSameAs($existing->end)) {
                    // $cut completely covers $existing, so $existing is removed completely
                } else {
                    // some of the beginning of $existing is removed by $cut
                    $new_ranges[] = new DateRange($cut->end->nextDay(), $existing->end);
                }
            } else if ($cut->end->isAfterOrSameAs($existing->end)) {
                // some of the end of $existing is removed by $cut
                $new_ranges[] = new DateRange($existing->start, $cut->start->prevDay());
            } else {
                // only remaining possibility is that $cut is completely contained within $existing
                // so it must split $existing into 2 disjoint ranges
                $new_ranges[] = new DateRange($existing->start, $cut->start->prevDay());
                $new_ranges[] = new DateRange($cut->end->nextDay(), $existing->end);
            }
            $i++;
        }

        // Any remaining ranges must be completely after $cut, so are unchanged
        while ($i < $num) {
            $new_ranges[] = $ranges[$i];
            $i++;
        }

        return $new_ranges;
    }

    /**
     * Utility function for determining the ranges that are in the intersection of 2 date sets
     *
     * Used internally by DateSet and MutableDateSet objects
     * The returned array of ranges is normalized (sorted and disjoint)
     *
     * @param BaseDateSet $a
     * @param BaseDateSet $b
     * @return DateRange[] Normalised list of ranges in the intersection of $a and $b
     */
    protected static function getIntersectingRanges(BaseDateSet $a, BaseDateSet $b)
    {
        $intersection = [];
        foreach ($a->ranges as $range_a) {
            foreach ($b->ranges as $range_b) {
                $intersection[] = DateRange::intersection($range_a, $range_b);
            }
        }
        return BaseDateSet::normalizeRanges(array_filter($intersection));
    }

    /**
     * Utility function to 'normalize' a list of ranges
     *
     * This means merging together any touching or overlapping ranges, and sorting into date order.
     * The resulting array of ranges are guaranteed to be sorted and disjoint.
     * Used internally by DateSet and MutableDate set objects.
     *
     * @param DateRange[] $in
     * @return DateRange[]
     */
    protected static function normalizeRanges(array $in)
    {
        $num = count($in);
        if ($num == 0) {
            return [];
        }

        usort($in, function ($a, $b) {
            return $a->start->timestamp - $b->start->timestamp;
        });

        $out = [];
        $curr = $in[0];
        for ($i = 1; $i < $num; $i++) {
            $next = $in[$i];
            if ($next->start->isAfter($curr->end->nextDay())) {
                // there's a gap between $curr and $next
                $out[] = $curr;
                $curr = $next;
            } elseif ($next->end->isAfter($curr->end)) {
                // $next overlaps or touches the end of $curr, so extend $curr
                $curr = new DateRange($curr->start, $next->end);
            } else {
                // $next is completely contained within $curr
            }
        }
        $out[] = $curr;

        return $out;
    }

    /**
     * Determine whether the given date is a member of this set
     *
     * @param JustDate $date
     * @return bool
     */
    public function includes(JustDate $date) : bool
    {
        foreach ($this->ranges as $range) {
            if ($range->end->isAfterOrSameAs($date)) {
                if ($range->start->isBeforeOrSameAs($date)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Get a generator which yields each range in the set as a DateRange object
     *
     * @return Generator
     */
    public function eachRange(): Generator
    {
        foreach ($this->ranges as $range) {
            yield $range;
        }
    }

    /**
     * Get a generator which yields each date in the set as a JustDate object
     *
     * @return Generator
     */
    public function eachDate(): Generator
    {
        foreach ($this->ranges as $range) {
            yield from $range->each();
        }
    }

    /**
     * Get the string representation of this set
     *
     * @return string
     */
    public function __toString() : string
    {
        return implode(', ', array_map(function (DateRange $range) {
            return (string) ($range->start->isSameAs($range->end) ? $range->start : $range);
        }, $this->ranges));
    }

    /**
     * Get the normalized list of ranges as a plain PHP array
     *
     * @return DateRange[]
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }
}