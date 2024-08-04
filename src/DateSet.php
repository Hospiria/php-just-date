<?php

namespace MadisonSolutions\JustDate;

/**
 * Class DateSet
 *
 * Class for storing a set of unique dates
 * Internally represented as a list of sorted, disjoint DateRange objects
 * This object is immutable - the dates in the set cannot be altered after the object is created.
 * (although new sets can be constructed from other sets via the union(), and intersection() methods etc)
 */
class DateSet extends BaseDateSet
{
    /**
     * Create a DateSet
     *
     * The dates that are included in the set can be defined by supplying any number of JustDate, DateRange,
     * DateSet or MutableDateSet objects (or any other class implementing DateRangeList) as parameters.
     */
    public function __construct(DateRangeList ...$lists)
    {
        $ranges = [];
        foreach ($lists as $list) {
            $ranges = array_merge($ranges, $list->getRanges());
        }
        $this->ranges = BaseDateSet::normalizeRanges($ranges);
    }

    /**
     * Alternative way of constructing a DateSet object that is optimised for creating from JustDate objects
     *
     * @param  JustDate  ...$dates  Dates that should be included in the set
     */
    public static function fromDates(JustDate ...$dates): DateSet
    {
        $instance = new DateSet;
        $instance->ranges = BaseDateSet::sortedRangesFromSingleDates(...$dates);
        return $instance;
    }

    /**
     * Create a new DateSet whose dates are the union of all of the dates in the supplied objects
     *
     * Note this is functionally identical to the standard new DateSet() constructor and is included just
     * for code readability and contrast with the complementary DateSet::intersection() function.
     */
    public static function union(DateRangeList ...$lists): DateSet
    {
        return new DateSet(...$lists);
    }

    /**
     * Create a new DateSet which is the intersection of the supplied objects
     *
     * The dates in the resulting DateSet will be those dates which are included in every one of the arguments
     */
    public static function intersection(DateRangeList ... $lists): DateSet
    {
        $num = count($lists);
        if ($num == 0) {
            return new DateSet;
        }
        $curr = new DateSet($lists[0]);
        for ($i = 1; $i < $num; $i++) {
            $next = new DateSet($lists[$i]);
            $curr->ranges = BaseDateSet::getIntersectingRanges($curr, $next);
        }
        return $curr;
    }
}
