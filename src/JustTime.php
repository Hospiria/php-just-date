<?php

namespace MadisonSolutions\JustDate;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;
use Serializable;

class JustTime implements Serializable, JsonSerializable
{
    /**
     * Create a new JustTime object from a DateTime object
     *
     * @param DateTime $date The DateTime object (remains unchanged)
     * @return MadisonSolutions\JustDate\JustTime The new JustTime instance
     */
    public static function fromDateTime(DateTime $date) : JustTime
    {
        return new JustTime((int) $date->format('H'), (int) $date->format('i'), (int) $date->format('s'));
    }

    /**
     * Get the current time
     *
     * @param ?DateTimeZone $timezone Optional timezone - if specified the time will be whatever the time is right now in the specified timezone
     * @return MadisonSolutions\JustDate\JustTime The new JustTime instance
     */
    public static function now(?DateTimeZone $timezone = null) : JustTime
    {
        return JustTime::fromDateTime(new DateTime('now', $timezone));
    }

    /**
     * Get the time at the specified timestamp
     *
     * @param ?DateTimeZone $timezone Optional timezone - if specified the time will be whatever the time is in the specified timezone at the specified timestamp
     * @return MadisonSolutions\JustDate\JustTime The new JustTime instance
     */
    public static function fromTimestamp(int $timestamp, ?DateTimeZone $timezone = null) : JustTime
    {
        return JustTime::fromDateTime((new DateTime(null, $timezone))->setTimestamp($timestamp));
    }

    /**
     * Create a new JustTime object from a string in H:i:s format
     *
     * @param string $his The date in H:i:s format, eg '14:35:02' (note seconds can be omitted eg '14:35')
     * @throws InvalidArgumentException If the string does not contain a valid time in H:i:s format
     * @return MadisonSolutions\JustDate\JustTime The new JustTime instance
     */
    public static function fromHis(string $his) : JustTime
    {
        return new JustTime(...JustTime::parseHis($his));
    }

    /**
     * Get hours minutes and seconds integers from a string in H:i:s format, if valid
     *
     * @param string $his The date in H:i:s format, eg '14:35' (note seconds can be omitted eg '14:35')
     * @throws InvalidArgumentException If the string does not contain a valid date in Y-m-d format
     * @return array Array containing integers [year, month, day]
     */
    public static function parseHis(string $his)
    {
        if (preg_match('/^(\d\d?):(\d\d?)(:(\d\d?))?$/', trim($his), $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) ($matches[4] ?? 0);
            if ($hours >= 0 && $hours < 24 && $minutes >= 0 && $minutes < 60 && $seconds >= 0 && $seconds < 60) {
                return [$hours, $minutes, $seconds];
            }
        }
        throw new InvalidArgumentException("Invalid H:i:s time '{$his}'");
    }

    /**
     * Return the earliest of a set of times
     *
     * @param MadisonSolutions\JustDate\JustTime $first
     * @param MadisonSolutions\JustDate\JustTime ...$others
     * @return MadisonSolutions\JustDate\JustTime The earliest time from $first and $others
     */
    public static function earliest(JustTime $first, JustTime ...$others) : JustTime
    {
        $earliest = $first;
        foreach ($others as $time) {
            if ($time->isBefore($earliest)) {
                $earliest = $time;
            }
        }
        return $earliest;
    }

    /**
     * Return the latest of a set of times
     *
     * @param MadisonSolutions\JustDate\JustTime $first
     * @param MadisonSolutions\JustDate\JustTime ...$others
     * @return MadisonSolutions\JustDate\JustTime The latest time from $first and $others
     */
    public static function latest(JustTime $first, JustTime ...$others) : JustTime
    {
        $latest = $first;
        foreach ($others as $time) {
            if ($time->isAfter($latest)) {
                $latest = $time;
            }
        }
        return $latest;
    }

    /**
     * Return the quotient and remainder when dividing integer $a by integer $b
     *
     * This differs from the PHP intdiv function by always returning a non-negative remainder
     * Eg quotientAndRemainder(-10, 60) returns quotient -1 and remainder 50
     * This makes it suitable for 'clock' calculations (-10 minutes is equivalent to 50 minutes from the previous hour)
     *
     * @param int $a the dividend
     * @param int $b the divisor
     * @return array Returns an array [0 => (int) quotient, 1 => (int) remainder]
     * @throws DivisionByZeroError If $b is zero
     */
    public static function quotientAndRemainder(int $a, int $b)
    {
        if ($a < 0) {
            $c = ceil(-$a / $b);
            return [-$c, $a + ($b * $c)];
        } else {
            return [intdiv($a, $b), $a % $b];
        }
    }

    /**
     * Create a new JustTime instance
     *
     * Note that once created, the JustTime is immutable, there's no way to alter the internal date.
     * It is possible to supply numerical values which are outside of the normal ranges and
     * the internal date value will be adjusted to correspond.
     * eg supplying 10:65:00 will result in 11:05:00
     * eg supplying 26:-10:00 will result in 01:50:00
     *
     * @param int $hours The hours (0 - 23)
     * @param int $minutes The minutes (0 - 59)
     * @param int $seconds The seconds (0 - 59)
     */
    public function __construct(int $hours = 0, int $minutes = 0, int $seconds = 0)
    {
        list($q, $r) = self::quotientAndRemainder($seconds, 60);
        $this->seconds = $r;
        $minutes += $q;
        list($q, $r) = self::quotientAndRemainder($minutes, 60);
        $this->minutes = $r;
        $hours += $q;
        list($q, $r) = self::quotientAndRemainder($hours, 24);
        $this->hours = $r;
    }

    /**
     * Getters
     *
     * hours - the hour as an integer (0 - 23)
     * minutes - the minutes as an integer (0 - 59)
     * seconds - the seconds as an integer (0 - 59)
     * since_midnight - the number of seconds from midnight to this time
     */
    public function __get($name)
    {
        switch ($name) {
            case 'hours':
                return $this->hours;
            case 'minutes':
                return $this->minutes;
            case 'seconds':
                return $this->seconds;
            case 'since_midnight':
                return ($this->hours * 60 * 60) + ($this->minutes * 60) + ($this->seconds);
        }
    }

    public function __isset($name)
    {
        switch ($name) {
            case 'hours':
            case 'minutes':
            case 'seconds':
            case 'since_midnight':
                return true;
        }
        return false;
    }

    /**
     * Standard string representation is H:i:s format
     */
    public function __toString()
    {
        return $this->format('H:i:s');
    }

    /**
     * Create a string representation of the time, with the given format
     *
     * Note that any date values which are requested in the format will have values from the Unix epoch - Jan 1st 1970
     *
     * @param string $format The format, as per PHP's date() function
     * @return string The formatted string
     */
    public function format(string $format = 'H:i:s') : string
    {
        static $utc = null;
        if (is_null($utc)) {
            $utc = new DateTimeZone('UTC');
        }
        return (new DateTime(null, $utc))->setTimestamp($this->since_midnight)->format($format);
    }

    /**
     * Add the specified number of hours, minutes and seconds to this time, and return a new JustTime object for the result
     *
     * Note values will wrap around midnight. Eg if you add 2 hours to 23:30:00 you'll get 01:30:00.
     * (This implies that sometimes adding positive values can lead to a time which is considered 'before' the original)
     * Note any of the values can be negative to subtract that amount of time instead of adding
     *
     * @param int $hours The number of hours to add
     * @param int $minutes The number of minutes to add
     * @param int $seconds The number of seconds to add
     *
     * @return MadisonSolutions\JustDate\JustTime The new JustTime object
     */
    public function addTime(int $hours = 0, int $minutes = 0, int $seconds = 0) : JustTime
    {
        return new JustTime($this->hours + $hours, $this->minutes + $minutes, $this->seconds + $seconds);
    }

    /**
     * Test whether a JustTime object refers to the same time as this one
     *
     * @param MadisonSolutions\JustDate\JustTime $other
     * @return bool True if $other is the same time
     */
    public function isSameAs(JustTime $other)
    {
        return $this->since_midnight == $other->since_midnight;
    }

    /**
     * Test whether a JustTime object refers to a time before this one
     *
     * @param MadisonSolutions\JustDate\JustTime $other
     * @return bool True if $other is before this time
     */
    public function isBefore(JustTime $other)
    {
        return $this->since_midnight < $other->since_midnight;
    }

    /**
     * Test whether a JustTime object refers to a time before or equal to this one
     *
     * @param MadisonSolutions\JustDate\JustTime $other
     * @return bool True if $other is before or the same as this date
     */
    public function isBeforeOrSameAs(JustTime $other)
    {
        return $this->since_midnight <= $other->since_midnight;
    }

    /**
     * Test whether a JustTime object refers to a time after this one
     *
     * @param MadisonSolutions\JustDate\JustTime $other
     * @return bool True if $other is after this date
     */
    public function isAfter(JustTime $other)
    {
        return $this->since_midnight > $other->since_midnight;
    }

    /**
     * Test whether a JustTime object refers to a time after or equal to this one
     *
     * @param MadisonSolutions\JustDate\JustTime $other
     * @return bool True if $other is after or the same as this time
     */
    public function isAfterOrSameAs(JustTime $other)
    {
        return $this->since_midnight >= $other->since_midnight;
    }

    /**
     * Serialization of a JustTime will consist of the H:i:s string
     */
    public function serialize()
    {
        return (string) $this;
    }

    /**
     * Unserialize by parsing the H:i:s string
     */
    public function unserialize($data)
    {
        $this->__construct(...JustTime::parseHis($data));
    }

    /**
     * Json serialize to the H:i:s string
     */
    public function jsonSerialize()
    {
        return (string) $this;
    }
}
