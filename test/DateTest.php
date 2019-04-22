<?php

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

    public function testCreateJustDates()
    {
        $d = new JustDate(2019, 4, 21);
        $this->assertInstanceOf(JustDate::class, $d);
        $this->assertSame('2019-04-21', (string) $d);
    }

    public function testCreatefromDateTime()
    {
        // Create a PHP DateTime object with the given date, time and timezone
        $p1 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Australia/Sydney'));

        // The resulting JustDate object should have the matching date with no time or timezone info
        $d1 = JustDate::fromDateTime($p1);
        $this->assertSame('2019-04-21', (string) $d1);
        $this->assertSame(gmmktime(0, 0, 0, 4, 21, 2019), $d1->timestamp);

        // A DateTime with the same date and time in a different timezone will naturally have a different timestamp
        // Since it refers to a different instance in time
        $p2 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Asia/Calcutta'));
        $this->assertNotEquals($p1->getTimestamp(), $p2->getTimestamp());

        // ...however it should will product an equal JustDate object
        $d2 = JustDate::fromDateTime($p2);
        $this->assertSame('2019-04-21', (string) $d2);
        $this->assertSame($d1->timestamp, $d2->timestamp);
        $this->assertTrue($d1->isSameAs($d2));
    }

    public function testCreateToday()
    {
        $d1 = JustDate::today();
        $this->assertSame(date('Y-m-d'), (string) $d1);

        $d2 = JustDate::today(new DateTimeZone('UTC'));
        $this->assertSame(gmdate('Y-m-d'), (string) $d2);

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
        $this->assertSame('2019-04-21', (string) $d1);

        // Same timestamp but with a timezone explicitly set
        $d2 = JustDate::fromTimestamp($ts, new DateTimeZone('Europe/London'));
        $this->assertSame('2019-04-21', (string) $d2);

        // At the same time in Sydney, it will already be the 22nd April
        $d3 = JustDate::fromTimestamp($ts, new DateTimeZone('Australia/Sydney'));
        $this->assertSame('2019-04-22', (string) $d3);
    }

    public function testCreateFromYmd()
    {
        $d1 = JustDate::fromYmd('2019-04-21');
        $this->assertEquals('2019-04-21', (string) $d1);
    }

    public function testCannotCreateFromInvalidYmd()
    {
        $this->assertThrows(InvalidArgumentException::class, function() {
            JustDate::fromYmd('foo');
        });

        $this->assertThrows(InvalidArgumentException::class, function() {
            JustDate::fromYmd('19-04-21');
        });

        $this->assertThrows(InvalidArgumentException::class, function() {
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
        $this->assertSame('2019-04-22', (string) $d1->addDays(1));
        $this->assertSame('2019-04-22', (string) $d1->nextDay());
        $this->assertSame('2019-05-01', (string) $d1->addDays(10));
        $this->assertSame('2020-02-15', (string) $d1->addDays(300));
        $this->assertSame('2019-04-20', (string) $d1->addDays(-1));
        $this->assertSame('2019-04-20', (string) $d1->prevDay());
        $this->assertSame('2018-06-25', (string) $d1->addDays(-300));
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
        $this->assertSame('2019-03-31', (string) $d1);

        $d2 = new JustDate(2019, 1, 0);
        $this->assertSame('2018-12-31', (string) $d2);
    }

    public function testStartAndEndOfMonths()
    {
        $d1 = new JustDate(2019, 04, 21);
        $this->assertSame('2019-04-01', (string) $d1->startOfMonth());
        $this->assertSame('2019-04-30', (string) $d1->endOfMonth());
    }
}
