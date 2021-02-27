<?php

namespace MadisonSolutions\JustDate;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;
use Serializable;

class JustDate implements Serializable, JsonSerializable
{
    /**
     * Create a new JustDate object from a DateTime object
     *
     * @param DateTime $date The DateTime object (remains unchanged)
     * @return MadisonSolutions\JustDate\JustDate The new JustDate instance
     */
    public static function fromDateTime(DateTime $date) : JustDate
    {
        return new JustDate((int) $date->format('Y'), (int) $date->format('m'), (int) $date->format('d'));
    }

    /**
     * Get the date that it is today
     *
     * @param ?DateTimeZone $timezone Optional timezone - if specified the date will be whatever the date is right now in the specified timezone
     * @return MadisonSolutions\JustDate\JustDate The new JustDate instance
     */
    public static function today(?DateTimeZone $timezone = null) : JustDate
    {
        return JustDate::fromDateTime(new DateTime('now', $timezone));
    }

    /**
     * Get the date at the specified timestamp
     *
     * @param ?DateTimeZone $timezone Optional timezone - if specified the date will be whatever the date is in the specified timezone at the specified timestamp
     * @return MadisonSolutions\JustDate\JustDate The new JustDate instance
     */
    public static function fromTimestamp(int $timestamp, ?DateTimeZone $timezone = null) : JustDate
    {
        return JustDate::fromDateTime((new DateTime(null, $timezone))->setTimestamp($timestamp));
    }

    /**
     * Create a new JustDate object from a string in Y-m-d format
     *
     * @param string $ymd The date in Y-m-d format, eg '2019-04-21'
     * @throws InvalidArgumentException If the string does not contain a valid date in Y-m-d format
     * @return MadisonSolutions\JustDate\JustDate The new JustDate instance
     */
    public static function fromYmd(string $ymd) : JustDate
    {
        return new JustDate(...JustDate::parseYmd($ymd));
    }

    /**
     * Get year month and day integers from a string in Y-m-d format, if valid
     *
     * @param string $ymd The date in Y-m-d format, eg '2019-04-21'
     * @throws InvalidArgumentException If the string does not contain a valid date in Y-m-d format
     * @return array Array containing integers [year, month, day]
     */
    public static function parseYmd(string $ymd)
    {
        if (preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)$/', trim($ymd), $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            if (checkdate($month, $day, $year)) {
                return [$year, $month, $day];
            }
        }
        throw new InvalidArgumentException("Invalid Y-m-d date '{$ymd}'");
    }

    /**
     * Count the number of days from $from to $to
     *
     * Note if the supplied $to date is before the $from date, the result will be negative
     *
     * @param MadisonSolutions\JustDate\JustDate $from The start date
     * @param MadisonSolutions\JustDate\JustDate $to The end date
     * @return int The number of days from $from to $to
     */
    public static function spanDays(JustDate $from, JustDate $to) : int
    {
        return (int) round(($to->timestamp - $from->timestamp) / (60 * 60 * 24));
    }

    /**
     * Return the earliest of a set of dates
     *
     * @param MadisonSolutions\JustDate\JustDate $first
     * @param MadisonSolutions\JustDate\JustDate ...$others
     * @return MadisonSolutions\JustDate\JustDate The earliest date from $first and $others
     */
    public static function earliest(JustDate $first, JustDate ...$others) : JustDate
    {
        $earliest = $first;
        foreach ($others as $date) {
            if ($date->isBefore($earliest)) {
                $earliest = $date;
            }
        }
        return $earliest;
    }

    /**
     * Return the latest of a set of dates
     *
     * @param MadisonSolutions\JustDate\JustDate $first
     * @param MadisonSolutions\JustDate\JustDate ...$others
     * @return MadisonSolutions\JustDate\JustDate The latest date from $first and $others
     */
    public static function latest(JustDate $first, JustDate ...$others) : JustDate
    {
        $latest = $first;
        foreach ($others as $date) {
            if ($date->isAfter($latest)) {
                $latest = $date;
            }
        }
        return $latest;
    }

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * Create a new JustDate instance
     *
     * Note that once created, the JustDate is immutable, there's no way to alter the internal date.
     * It is possible to supply numerical values which are outside of the normal ranges and
     * the internal date value will be adjusted to correspond.
     * eg supplying 0 for the $day will result in the last day of the previous month.
     *
     * @param int $year The Year (full, 4 digit year)
     * @param int $month The month (1 = January ... 12 = December)
     * @param int $day The day of the month (first day is 1)
     */
    public function __construct(int $year, int $month, int $day)
    {
        static $utc = null;
        if (is_null($utc)) {
            $utc = new DateTimeZone('UTC');
        }
        $this->date = (new DateTime(null, $utc))->setDate($year, $month, $day)->setTime(0, 0, 0, 0);
    }

    /**
     * Getters
     *
     * year - the year as an integer
     * month - the month as an integer (1 = January ... 12 = December)
     * day - the day of the month as an integer
     * day_of_week - the day of the week (0 = Sunday ... 6 = Saturday)
     * timestamp - unix timestamp corresponding to 00:00:00 on this date in UTC
     */
    public function __get($name)
    {
        switch ($name) {
            case 'year':
                return (int) $this->date->format('Y');
            case 'month':
                return (int) $this->date->format('m');
            case 'day':
                return (int) $this->date->format('d');
            case 'day_of_week':
                return (int) $this->date->format('w');
            case 'timestamp':
                return (int) $this->date->getTimestamp();
        }
    }

    public function __isset($name)
    {
        switch ($name) {
            case 'year':
            case 'month':
            case 'day':
            case 'day_of_week':
            case 'timestamp':
                return true;
        }
        return false;
    }

    /**
     * Standard string representation is Y-m-d format
     */
    public function __toString()
    {
        return $this->date->format('Y-m-d');
    }

    /**
     * Create a string representation of the date, with the given format
     *
     * Note that any time values which are requested in the format will always be zero
     *
     * @param string $format The format, as per PHP's date() function
     * @return string The formatted string
     */
    public function format(string $format = 'Y-m-d') : string
    {
        return $this->date->format($format);
    }

    /**
     * Add the specified number of days to this date, and return a new JustDate object for the result
     *
     * @param int $days The number of days to add (use negative values to get earlier dates)
     * @return MadisonSolutions\JustDate\JustDate The new JustDate object
     */
    public function addDays(int $days) : JustDate
    {
        return new JustDate($this->year, $this->month, $this->day + $days);
    }

    /**
     * Get the next day after this one
     *
     * @return MadisonSolutions\JustDate\JustDate The new JustDate object
     */
    public function nextDay() : JustDate
    {
        return $this->addDays(1);
    }

    /**
     * Get the day prior to this one
     *
     * @return MadisonSolutions\JustDate\JustDate The new JustDate object
     */
    public function prevDay() : JustDate
    {
        return $this->addDays(-1);
    }

    /**
     * Get the date which is the start of this date's month
     *
     * @return MadisonSolutions\JustDate\JustDate The new JustDate object
     */
    public function startOfMonth() : JustDate
    {
        return new JustDate($this->year, $this->month, 1);
    }

    /**
     * Get the date which is the end of this date's month
     *
     * @return MadisonSolutions\JustDate\JustDate The new JustDate object
     */
    public function endOfMonth() : JustDate
    {
        return new JustDate($this->year, $this->month + 1, 0);
    }

    /**
     * Test whether a JustDate object refers to the same date as this one
     *
     * @param MadisonSolutions\JustDate\JustDate $other
     * @return bool True if $other is the same date
     */
    public function isSameAs(JustDate $other)
    {
        return $this->timestamp == $other->timestamp;
    }

    /**
     * Test whether a JustDate object refers to a date before this one
     *
     * @param MadisonSolutions\JustDate\JustDate $other
     * @return bool True if $other is before this date
     */
    public function isBefore(JustDate $other)
    {
        return $this->timestamp < $other->timestamp;
    }

    /**
     * Test whether a JustDate object refers to a date before or equal to this one
     *
     * @param MadisonSolutions\JustDate\JustDate $other
     * @return bool True if $other is before or the same as this date
     */
    public function isBeforeOrSameAs(JustDate $other)
    {
        return $this->timestamp <= $other->timestamp;
    }

    /**
     * Test whether a JustDate object refers to a date after this one
     *
     * @param MadisonSolutions\JustDate\JustDate $other
     * @return bool True if $other is after this date
     */
    public function isAfter(JustDate $other)
    {
        return $this->timestamp > $other->timestamp;
    }

    /**
     * Test whether a JustDate object refers to a date after or equal to this one
     *
     * @param MadisonSolutions\JustDate\JustDate $other
     * @return bool True if $other is after or the same as this date
     */
    public function isAfterOrSameAs(JustDate $other)
    {
        return $this->timestamp >= $other->timestamp;
    }

    /**
     * Is the date a Sunday
     *
     * @return bool True if the date is a Sunday, false otherwise
     */
    public function isSunday()
    {
        return $this->day_of_week == 0;
    }

    /**
     * Is the date a Monday
     *
     * @return bool True if the date is a Monday, false otherwise
     */
    public function isMonday()
    {
        return $this->day_of_week == 1;
    }

    /**
     * Is the date a Tuesday
     *
     * @return bool True if the date is a Tuesday, false otherwise
     */
    public function isTuesday()
    {
        return $this->day_of_week == 2;
    }

    /**
     * Is the date a Wednesday
     *
     * @return bool True if the date is a Wednesday, false otherwise
     */
    public function isWednesday()
    {
        return $this->day_of_week == 3;
    }

    /**
     * Is the date a Thursday
     *
     * @return bool True if the date is a Thursday, false otherwise
     */
    public function isThursday()
    {
        return $this->day_of_week == 4;
    }

    /**
     * Is the date a Friday
     *
     * @return bool True if the date is a Friday, false otherwise
     */
    public function isFriday()
    {
        return $this->day_of_week == 5;
    }

    /**
     * Is the date a Saturday
     *
     * @return bool True if the date is a Saturday, false otherwise
     */
    public function isSaturday()
    {
        return $this->day_of_week == 6;
    }

    /**
     * Is the date a Weekday (Monday to Friday)
     *
     * @return bool True if the date is a Weekday, false otherwise
     */
    public function isWeekday()
    {
        $dow = $this->day_of_week;
        return $dow > 0 && $dow < 6;
    }

    /**
     * Is the date a Weekend (Saturday or Sunday)
     *
     * @return bool True if the date is a Saturday or Sunday, false otherwise
     */
    public function isWeekend()
    {
        $dow = $this->day_of_week;
        return $dow == 0 || $dow == 6;
    }

    /**
     * Serialization of a JustDate will consist of the Y-m-d string
     */
    public function serialize()
    {
        return (string) $this;
    }

    /**
     * Unserialize by parsing the Y-m-d string
     */
    public function unserialize($data)
    {
        $this->__construct(...JustDate::parseYmd($data));
    }

    /**
     * Json serialize to the Y-m-d string
     */
    public function jsonSerialize()
    {
        return (string) $this;
    }
}
