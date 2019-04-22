<?php

use MadisonSolutions\JustDate\DateRange;
use MadisonSolutions\JustDate\JustDate;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    // Helper method of verifying the expected exception is thrown when the callback is executed
    protected function assertThrows(string $exceptionClass, callable $callback)
    {
        $e = null;
        try {
            $callback();
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf($exceptionClass, $e);
    }

    protected function assertJustDate(string $expectedYmd, $actual)
    {
        $this->assertInstanceOf(JustDate::class, $actual);
        $this->assertSame($expectedYmd, (string) $actual);
    }

    protected function assertDateRange(string $expectedYmd, $actual)
    {
        $this->assertInstanceOf(DateRange::class, $actual);
        $this->assertSame($expectedYmd, (string) $actual);
    }

    public function testCreateJustDates()
    {
        $d = new JustDate(2019, 4, 21);
        $this->assertJustDate('2019-04-21', $d);
    }

    public function testCreatefromDateTime()
    {
        // Create a PHP DateTime object with the given date, time and timezone
        $p1 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Australia/Sydney'));

        // The resulting JustDate object should have the matching date with no time or timezone info
        $d1 = JustDate::fromDateTime($p1);
        $this->assertJustDate('2019-04-21', $d1);
        $this->assertSame(gmmktime(0, 0, 0, 4, 21, 2019), $d1->timestamp);

        // A DateTime with the same date and time in a different timezone will naturally have a different timestamp
        // Since it refers to a different instance in time
        $p2 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Asia/Calcutta'));
        $this->assertNotEquals($p1->getTimestamp(), $p2->getTimestamp());

        // ...however it should will product an equal JustDate object
        $d2 = JustDate::fromDateTime($p2);
        $this->assertJustDate('2019-04-21', $d2);
        $this->assertSame($d1->timestamp, $d2->timestamp);
        $this->assertTrue($d1->isSameAs($d2));
    }

    public function testCreateToday()
    {
        $d1 = JustDate::today();
        $this->assertJustDate(date('Y-m-d'), $d1);

        $d2 = JustDate::today(new DateTimeZone('UTC'));
        $this->assertJustDate(gmdate('Y-m-d'), $d2);

        // These Pacific timezones overlap each other, so it's never the same date in both
        $d3 = JustDate::today(new DateTimeZone('Pacific/Kiritimati'));
        $d4 = JustDate::today(new DateTimeZone('Pacific/Niue'));
        $this->assertNotEquals((string) $d3, (string) $d4);
    }

    public function testCreateFromTimestamp()
    {
        // Create the timesamp for 2019-04-21 16:23 in UTC
        $ts = gmmktime(16, 23, 12, 4, 21, 2019);

        // Create a JustDate from the timestamp
        $d1 = JustDate::fromTimestamp($ts);
        $this->assertJustDate('2019-04-21', $d1);

        // Same timestamp but with a timezone explicitly set
        $d2 = JustDate::fromTimestamp($ts, new DateTimeZone('Europe/London'));
        $this->assertJustDate('2019-04-21', $d2);

        // At the same time in Sydney, it will already be the 22nd April
        $d3 = JustDate::fromTimestamp($ts, new DateTimeZone('Australia/Sydney'));
        $this->assertJustDate('2019-04-22', $d3);
    }

    public function testCreateFromYmd()
    {
        $d1 = JustDate::fromYmd('2019-04-21');
        $this->assertJustDate('2019-04-21', $d1);
    }

    public function testCannotCreateFromInvalidYmd()
    {
        $this->assertThrows(InvalidArgumentException::class, function () {
            JustDate::fromYmd('foo');
        });

        $this->assertThrows(InvalidArgumentException::class, function () {
            JustDate::fromYmd('19-04-21');
        });

        $this->assertThrows(InvalidArgumentException::class, function () {
            JustDate::fromYmd('2019-21-04');
        });
    }

    public function testSpanDays()
    {
        $this->assertSame(2, JustDate::spanDays(new JustDate(2019, 04, 21), new JustDate(2019, 04, 23)));
        $this->assertSame(0, JustDate::spanDays(new JustDate(2019, 04, 21), new JustDate(2019, 04, 21)));
        $this->assertSame(-2, JustDate::spanDays(new JustDate(2019, 04, 23), new JustDate(2019, 04, 21)));
        $this->assertSame(365, JustDate::spanDays(new JustDate(2018, 04, 21), new JustDate(2019, 04, 21)));
    }

    public function testGetters()
    {
        $d = new JustDate(2019, 04, 21);
        $this->assertSame(2019, $d->year);
        $this->assertSame(4, $d->month);
        $this->assertSame(21, $d->day);
        $this->assertSame(0, $d->day_of_week); // Sunday
        $this->assertSame(gmmktime(0, 0, 0, 4, 21, 2019), $d->timestamp);
    }

    public function testAddDays()
    {
        $d1 = new JustDate(2019, 04, 21);
        $this->assertJustDate('2019-04-22', $d1->addDays(1));
        $this->assertJustDate('2019-04-22', $d1->nextDay());
        $this->assertJustDate('2019-05-01', $d1->addDays(10));
        $this->assertJustDate('2020-02-15', $d1->addDays(300));
        $this->assertJustDate('2019-04-20', $d1->addDays(-1));
        $this->assertJustDate('2019-04-20', $d1->prevDay());
        $this->assertJustDate('2018-06-25', $d1->addDays(-300));
    }

    public function testFormat()
    {
        $p1 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Australia/Sydney'));
        $d1 = JustDate::fromDateTime($p1);

        $this->assertSame('Sun April 21st 2019', $d1->format('D F jS Y'));
        $this->assertSame('Sun, 21 Apr 2019 00:00:00 +0000', $d1->format('r'));

        $d2 = $d1->addDays(3);
        $this->assertSame('Wed, 24 Apr 2019 00:00:00 +0000', $d2->format('r'));
    }

    public function testComparisons()
    {
        $d1 = new JustDate(2019, 04, 21);
        $d2 = new JustDate(2019, 04, 22);

        $this->assertTrue($d1->isBefore($d2));
        $this->assertFalse($d2->isBefore($d1));
        $this->assertTrue($d2->isAfter($d1));
        $this->assertFalse($d1->isAfter($d2));
        $this->assertTrue($d1->isBeforeOrSameAs($d1));
        $this->assertTrue($d1->isBeforeOrSameAs($d2));
        $this->assertFalse($d2->isBeforeOrSameAs($d1));
        $this->assertTrue($d2->isAfterOrSameAs($d2));
        $this->assertTrue($d2->isAfterOrSameAs($d1));
        $this->assertFalse($d1->isAfterOrSameAs($d2));

        $this->assertTrue($d1->isSameAs(JustDate::fromYmd('2019-04-21')));
    }

    public function testDateTrickery()
    {
        $d1 = new JustDate(2019, 04, 0); // day = 0 gives last day of prev month
        $this->assertJustDate('2019-03-31', $d1);

        $d2 = new JustDate(2019, 1, 0);
        $this->assertJustDate('2018-12-31', $d2);
    }

    public function testStartAndEndOfMonths()
    {
        $d1 = new JustDate(2019, 04, 21);
        $this->assertJustDate('2019-04-01', $d1->startOfMonth());
        $this->assertJustDate('2019-04-30', $d1->endOfMonth());
    }

    public function testEarliestAndLatest()
    {
        $d1 = new JustDate(2019, 04, 21);
        $this->assertJustDate('2019-04-21', JustDate::earliest($d1));
        $this->assertJustDate('2019-04-21', JustDate::latest($d1));
        $this->assertJustDate('2019-04-21', JustDate::earliest($d1, $d1));
        $this->assertJustDate('2019-04-21', JustDate::latest($d1, $d1));

        $d2 = new JustDate(2019, 04, 22);
        $this->assertJustDate('2019-04-21', JustDate::earliest($d1, $d2));
        $this->assertJustDate('2019-04-22', JustDate::latest($d1, $d2));

        $d3 = new JustDate(2019, 04, 23);
        $this->assertJustDate('2019-04-21', JustDate::earliest($d3, $d2, $d1));
        $this->assertJustDate('2019-04-23', JustDate::latest($d3, $d2, $d1));
    }

    public function testCreateRange()
    {
        $d1 = new JustDate(2019, 04, 21);
        $d2 = new JustDate(2019, 04, 25);
        $r = new DateRange($d1, $d2);
        $this->assertDateRange("2019-04-21 to 2019-04-25", $r);

        $r = DateRange::fromYmd('2019-04-21', '2019-04-25');
        $this->assertDateRange("2019-04-21 to 2019-04-25", $r);

        // Won't let you make a range with end before start
        $this->assertThrows(InvalidArgumentException::class, function () {
            $r = new DateRange(new JustDate(2019, 04, 21), new JustDate(2019, 04, 19));
        });
        $this->assertThrows(InvalidArgumentException::class, function () {
            $r = DateRange::fromYmd('2019-04-21', '2019-04-19');
        });

        // Start same as end is allowed though
        $r = new DateRange($d1, $d1);
        $this->assertDateRange("2019-04-21 to 2019-04-21", $r);
    }

    public function testRangeGetters()
    {
        $r1 = DateRange::fromYmd('2019-04-21', '2019-04-25');
        $this->assertJustDate('2019-04-21', $r1->start);
        $this->assertJustDate('2019-04-25', $r1->end);
        $this->assertSame(4, $r1->span);

        $r2 = DateRange::fromYmd('2019-04-21', '2019-04-21');
        $this->assertJustDate('2019-04-21', $r2->start);
        $this->assertJustDate('2019-04-21', $r2->end);
        $this->assertSame(0, $r2->span);
    }

    public function testRangeIncludes()
    {
        $r1 = DateRange::fromYmd('2019-04-21', '2019-04-25');
        $this->assertFalse($r1->includes(new JustDate(2019, 04, 20)));
        $this->assertTrue($r1->includes(new JustDate(2019, 04, 21)));
        $this->assertTrue($r1->includes(new JustDate(2019, 04, 23)));
        $this->assertTrue($r1->includes(new JustDate(2019, 04, 25)));
        $this->assertFalse($r1->includes(new JustDate(2019, 04, 27)));
    }

    public function testRangeIterators()
    {
        $r1 = DateRange::fromYmd('2019-04-21', '2019-04-25');

        $test_str = '';
        foreach ($r1->each() as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('2122232425', $test_str);

        $test_str = '';
        foreach ($r1->eachExceptEnd() as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('21222324', $test_str);

        $r2 = DateRange::fromYmd('2019-04-21', '2019-04-21');

        $test_str = '';
        foreach ($r2->each() as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('21', $test_str);

        $test_var = false;
        foreach ($r2->eachExceptEnd() as $date) {
            $test_var = true;
        }
        $this->assertSame(false, $test_var);
    }

    public function testRangeIntersectionAndContains()
    {
        $r1 = DateRange::fromYmd('2019-04-21', '2019-04-25');

        // Overlap before
        $r2 = DateRange::fromYmd('2019-04-18', '2019-04-23');
        $this->assertDateRange("2019-04-21 to 2019-04-23", DateRange::intersection($r1, $r2));
        $this->assertFalse($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        // Overlap after
        $r2 = DateRange::fromYmd('2019-04-22', '2019-04-28');
        $this->assertDateRange("2019-04-22 to 2019-04-25", DateRange::intersection($r1, $r2));
        $this->assertFalse($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        // Completely surrounding
        $r2 = DateRange::fromYmd('2019-04-22', '2019-04-24');
        $this->assertDateRange("2019-04-22 to 2019-04-24", DateRange::intersection($r1, $r2));
        $this->assertTrue($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        // No overlap
        $r2 = DateRange::fromYmd('2019-04-16', '2019-04-18');
        $this->assertNull(DateRange::intersection($r1, $r2));
        $this->assertFalse($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        $r2 = DateRange::fromYmd('2019-04-26', '2019-04-28');
        $this->assertNull(DateRange::intersection($r1, $r2));
        $this->assertFalse($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        // Endpoints only
        $r2 = DateRange::fromYmd('2019-04-21', '2019-04-21');
        $this->assertDateRange("2019-04-21 to 2019-04-21", DateRange::intersection($r1, $r2));
        $this->assertTrue($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        $r2 = DateRange::fromYmd('2019-04-25', '2019-04-25');
        $this->assertDateRange("2019-04-25 to 2019-04-25", DateRange::intersection($r1, $r2));
        $this->assertTrue($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));
    }

    public function testSerialization()
    {
        $d1 = new JustDate(2019, 04, 21);
        $s = serialize($d1);
        $this->assertTrue(is_string($s));
        $_d1 = unserialize($s);
        $this->assertJustDate('2019-04-21', $_d1);
        $this->assertNotSame($d1, $_d1);

        $d2 = new JustDate(2019, 04, 25);
        $r = new DateRange($d1, $d2);
        $s = serialize($r);
        $this->assertTrue(is_string($s));
        $_r = unserialize($s);
        $this->assertDateRange('2019-04-21 to 2019-04-25', $_r);
        $this->assertNotSame($r, $_r);

        $this->assertSame('"2019-04-21"', json_encode($d1));
        $this->assertSame('{"start":"2019-04-21","end":"2019-04-25"}', json_encode($r));
    }
}
