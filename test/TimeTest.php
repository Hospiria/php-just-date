<?php

use MadisonSolutions\JustDate\JustTime;
use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
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

    protected function assertJustTime(string $expectedHis, $actual)
    {
        $this->assertInstanceOf(JustTime::class, $actual);
        $this->assertSame($expectedHis, (string) $actual);
    }

    public function testCreateJustTimes()
    {
        $t = new JustTime(2, 30, 40);
        $this->assertJustTime('02:30:40', $t);

        // try some out of range values
        $t = new JustTime(0, 120, 90);
        $this->assertJustTime('02:01:30', $t);

        $t = new JustTime(2, 65, -10);
        $this->assertJustTime('03:04:50', $t);

        $t = new JustTime(23, 90, 40);
        $this->assertJustTime('00:30:40', $t);

        $t = new JustTime(25, 61, 61);
        $this->assertJustTime('02:02:01', $t);

        $t = new JustTime(-1, -1, -1);
        $this->assertJustTime('22:58:59', $t);
    }

    public function testCreatefromDateTime()
    {
        // Create a PHP DateTime object with the given date, time and timezone
        $p1 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Australia/Sydney'));

        // The resulting JustTime object should have the matching time and no date info
        $t1 = JustTime::fromDateTime($p1);
        $this->assertJustTime('16:23:12', $t1);
        $this->assertSame(gmmktime(16, 23, 12, 1, 1, 1970), $t1->since_midnight);
    }

    public function testCreateNow()
    {
        $t1 = JustTime::now();
        $this->assertJustTime(date('H:i:s'), $t1);

        // Get the UTC time now and check it's correct
        $utcZone = new DateTimeZone('UTC');
        $t2 = JustTime::now($utcZone);
        $this->assertJustTime(gmdate('H:i:s'), $t2);

        // Get the time now in Kathmandu and check it's correct
        $kmdZone = new DateTimeZone('Asia/Kathmandu');
        $t3 = JustTime::now($kmdZone);
        $kmdNow = new DateTime('now', $kmdZone);
        $this->assertJustTime($kmdNow->format('H:i:s'), $t3);
    }

    public function testCreateFromTimestamp()
    {
        // Create the timesamp for 2019-04-21 16:23 in UTC
        $ts = gmmktime(16, 23, 12, 4, 21, 2019);

        // Create a JustTime from the timestamp
        // With no timezone defined, we should be the local system time
        $t1 = JustTime::fromTimestamp($ts);
        $this->assertJustTime(date('H:i:s', $ts), $t1);

        // Same timestamp but with UTC timezone explicitly set
        $t2 = JustTime::fromTimestamp($ts, new DateTimeZone('UTC'));
        $this->assertJustTime('16:23:12', $t2);

        // Same timestamp but with Sydney timezone explicitly set
        $sydZone = new DateTimeZone('Australia/Sydney');
        $t3 = JustTime::fromTimestamp($ts, $sydZone);
        $sydTime = (new DateTime(null, $sydZone))->setTimestamp($ts);
        $this->assertJustTime($sydTime->format('H:i:s'), $t3);
    }

    public function testCreateFromHis()
    {
        $t = JustTime::fromHis('2:3:4');
        $this->assertJustTime('02:03:04', $t);

        $t = JustTime::fromHis('02:03:04');
        $this->assertJustTime('02:03:04', $t);

        $t = JustTime::fromHis('18:35:40');
        $this->assertJustTime('18:35:40', $t);

        $t = JustTime::fromHis('2:30');
        $this->assertJustTime('02:30:00', $t);
    }

    public function testCannotCreateFromInvalidHis()
    {
        $this->assertThrows(InvalidArgumentException::class, function () {
            JustTime::fromHis('foo');
        });

        $this->assertThrows(InvalidArgumentException::class, function () {
            JustTime::fromHis('25:30:00');
        });

        $this->assertThrows(InvalidArgumentException::class, function () {
            JustTime::fromHis('16:99:02');
        });
    }

    public function testGetters()
    {
        $t = new JustTime(16, 35, 17);
        $this->assertSame(16, $t->hours);
        $this->assertSame(35, $t->minutes);
        $this->assertSame(17, $t->seconds);
        $this->assertSame(16 * 60 * 60 + 35 * 60 + 17, $t->since_midnight);
    }

    public function testAddTime()
    {
        $t1 = new JustTime(12, 00, 00);
        $this->assertJustTime('12:00:00', $t1->addTime(0, 0, 0));
        $this->assertJustTime('13:01:01', $t1->addTime(1, 1, 1));
        $this->assertJustTime('10:58:59', $t1->addTime(-1, -1, -1));
        $this->assertJustTime('00:00:00', $t1->addTime(12));
        $this->assertJustTime('09:41:30', $t1->addTime(20, 100, 90));
        $this->assertJustTime('14:00:00', $t1->addTime(0, 0, 60 * 60 * 2));
        $this->assertJustTime('10:00:00', $t1->addTime(0, 0, 60 * 60 * -2));
        $this->assertJustTime('22:10:10', $t1->addTime(-14, 10, 10));
    }

    public function testFormat()
    {
        $t1 = JustTime::fromHis('14:08:17');

        $this->assertSame('14:08:17', $t1->format('H:i:s'));
        $this->assertSame('2:08pm', $t1->format('g:ia'));
        $this->assertSame('Thu, 01 Jan 1970 14:08:17 +0000', $t1->format('r'));
    }

    public function testComparisons()
    {
        $t1 = new JustTime(14, 8, 17);
        $t2 = new JustTime(14, 8, 18);

        $this->assertTrue($t1->isBefore($t2));
        $this->assertFalse($t2->isBefore($t1));
        $this->assertTrue($t2->isAfter($t1));
        $this->assertFalse($t1->isAfter($t2));
        $this->assertTrue($t1->isBeforeOrSameAs($t1));
        $this->assertTrue($t1->isBeforeOrSameAs($t2));
        $this->assertFalse($t2->isBeforeOrSameAs($t1));
        $this->assertTrue($t2->isAfterOrSameAs($t2));
        $this->assertTrue($t2->isAfterOrSameAs($t1));
        $this->assertFalse($t1->isAfterOrSameAs($t2));

        $this->assertTrue($t1->isSameAs(JustTime::fromHis('14:08:17')));
    }

    public function testEarliestAndLatest()
    {
        $t1 = new JustTime(14, 8, 7);
        $this->assertJustTime('14:08:07', JustTime::earliest($t1));
        $this->assertJustTime('14:08:07', JustTime::latest($t1));
        $this->assertJustTime('14:08:07', JustTime::earliest($t1, $t1));
        $this->assertJustTime('14:08:07', JustTime::latest($t1, $t1));

        $t2 = new JustTime(14, 8, 8);
        $this->assertJustTime('14:08:07', JustTime::earliest($t1, $t2));
        $this->assertJustTime('14:08:08', JustTime::latest($t1, $t2));

        $t3 = new JustTime(14, 8, 6);
        $this->assertJustTime('14:08:06', JustTime::earliest($t3, $t2, $t1));
        $this->assertJustTime('14:08:08', JustTime::latest($t3, $t2, $t1));
    }

    public function testSerialization()
    {
        $t1 = new JustTime(14, 8, 7);
        $s = serialize($t1);
        $this->assertTrue(is_string($s));
        $_d1 = unserialize($s);
        $this->assertJustTime('14:08:07', $_d1);
        $this->assertNotSame($t1, $_d1);

        $this->assertSame('"14:08:07"', json_encode($t1));
    }
}
