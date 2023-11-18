<?php

namespace MadisonSolutions\JustDate;

/**
 * Class MutableDateSet
 *
 * Class for storing a set of unique dates
 * Internally represented as a list of sorted, disjoint DateRange objects
 * Unlike DateSet, the dates that are included in a MutableDateSet can be modified after the object is created
 *
 * @package MadisonSolutions\JustDate
 */
class MutableDateSet extends BaseDateSet
{
    /**
     * Create a MutableDateSet
     *
     * The dates that are initially included in the set can be defined by supplying any number of JustDate, DateRange,
     * DateSet or MutableDateSet objects (or any other class implementing DateRangeList) as parameters.
     *
     * @param DateRangeList ...$lists
     */
    public function __construct(DateRangeList ...$lists)
    {
        $this->ranges = [];
        foreach ($lists as $list) {
            $this->add($list);
        }
    }

    /**
     * Alternative way of constructing a MutableDateSet object that is optimised for creating from JustDate objects
     *
     * @param JustDate ...$dates Dates that should initially be included in the set
     * @return MutableDateSet
     */
    public static function fromDates(JustDate ...$dates) : MutableDateSet
    {
        $instance = new MutableDateSet();
        $instance->ranges = BaseDateSet::sortedRangesFromSingleDates(...$dates);
        return $instance;
    }

    /**
     * Create a new MutableDateSet whose dates are the union of all of the dates in the supplied objects
     *
     * Note this is functionally identical to the standard new MutableDateSet() constructor and is included just
     * for code readability and contrast with the complementary MutableDateSet::intersection() function.
     *
     * @param DateRangeList ...$lists
     * @return MutableDateSet
     */
    public static function union(DateRangeList ...$lists) : MutableDateSet
    {
        return new MutableDateSet(...$lists);
    }

    /**
     * Create a new MutableDateSet which is the intersection of the supplied objects
     *
     * The dates in the resulting MutableDateSet will be those dates which are included in every one of the arguments
     *
     * @param DateRangeList ...$lists
     * @return MutableDateSet
     */
    public static function intersection(DateRangeList... $lists) : MutableDateSet
    {
        $num = count($lists);
        if ($num == 0) {
            return new MutableDateSet();
        }
        $curr = new MutableDateSet($lists[0]);
        for ($i = 1; $i < $num; $i++) {
            $next = new MutableDateSet($lists[$i]);
            $curr->ranges = BaseDateSet::getIntersectingRanges($curr, $next);
        }
        return $curr;
    }

    /**
     * Add a Date or DateRange or set of dates to this set
     *
     * The dates contained in the supplied object will be added to this set.
     * Note the set is mutated by this function.
     * The updated set is returned for chaining.
     *
     * @param DateRangeList $list
     * @return $this
     */
    public function add(DateRangeList $list) : MutableDateSet
    {
        foreach ($list->getRanges() as $range) {
            $this->addRange($range);
        }
        return $this;
    }

    /**
     * Add the dates in a DateRange to this set
     *
     * The dates contained in the supplied object will be added to this set.
     * Note the set is mutated by this function.
     * The updated set is returned for chaining.
     *
     * @param DateRange $new
     * @return $this
     */
    public function addRange(DateRange $new) : MutableDateSet
    {
        $num = count($this->ranges);
        if ($num == 0) {
            $this->ranges = [$new];
            return $this;
        }

        $i = 0;
        while ($i < $num && $new->start->isAfter($this->ranges[$i]->end->nextDay())) {
            $i++;
        }

        if ($i == $num) {
            // $new is after all of the existing ranges
            array_push($this->ranges, $new);
            return $this;
        }

        // $new is before, touching, or overlapping range $i
        $start = JustDate::earliest($this->ranges[$i]->start, $new->start);

        $j = $i;
        while ($j < $num && $new->end->isAfterOrSameAs($this->ranges[$j]->start->prevDay())) {
            $j++;
        }

        // $new is entirely before (not touching) range $j
        $end = ($j == 0) ? $new->end : JustDate::latest($this->ranges[$j - 1]->end, $new->end);

        array_splice($this->ranges, $i, $j - $i, [DateRange::make($start, $end)]);
        return $this;
    }

    /**
     * Remove a Date or DateRange or set of dates from this set
     *
     * The dates contained in the supplied object will be removed from this set.
     * Note the set is mutated by this function.
     * The updated set is returned for chaining.
     *
     * @param DateRangeList $list_to_cut
     * @return $this
     */
    public function remove(DateRangeList $list_to_cut) : MutableDateSet
    {
        $ranges = $this->ranges;
        foreach ($list_to_cut->getRanges() as $range_to_cut) {
            $ranges = $this->subtractRangeFromSortedRanges($ranges, $range_to_cut);
        }
        $this->ranges = $ranges;
        return $this;
    }
}
