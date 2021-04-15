<?php

namespace MadisonSolutions\JustDate;

/**
 * Interface DateRangeList
 *
 * General interface for things that can be used as a list of DateRanges
 * This includes:
 * JustDate objects (a list containing single DateRange starting and ending on the date)
 * DateRange objects (a list containing a single DateRange)
 * DateSet objects (the list of included ranges)
 * MutableDateSet objects (the list of included ranges)
 *
 * @package MadisonSolutions\JustDate
 */
interface DateRangeList
{
    /**
     * Get the DateRange objects associated with this DateRangeList
     *
     * @return DateRange[] An array of DateRange objects
     */
    public function getRanges() : array;
}