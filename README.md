# php-just-date

PHP library for dealing with dates without any time information

There are several excellent PHP libraries for working with DateTime objects. But sometimes you need to deal with only the date part of a DateTime object.

For example, a hotel booking system might care only about a guest's check-in date - and the exact time of arrival is unimportant. In those situations, all of the functionality relevant to the time of day just gets in the way, making things like comparisons and equality checks awkward. Added complications like timezones and daylight saving time further muddy the waters.

This library aims to make it simple to work with just the date part.

### Install

`composer require madison-solutions/just-date`


### Basic Use

```
use MadisonSolutions\JustDate\JustDate;

$date = new JustDate(2019, 4, 21);

(string) $date;
// 2019-04-21

$date->format('D F jS Y');
// Sun April 21st 2019

$date->year;
// 2019
$date->month;
// 4
$date->day;
// 21
$date->day_of_week;
// 0

$date2 = JustDate::fromYmd('2019-04-22');
$date->isBefore($date2);
// true

```

### Time is ignored

```
use MadisonSolutions\JustDate\JustDate;

$t1 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Australia/Sydney'));
$t1->format('r');
// Sun, 21 Apr 2019 16:23:12 +1000

$d1 - JustDate::fromDateTime($t1);
$d1->format('r');
// Wed, 24 Apr 2019 00:00:00 +0000

// Different time, different timezone, but the date part is the same
$t2 = new DateTime('2019-04-21 19:05:47', new DateTimeZone('Europe/London'));
$d2 = JustDate::fromDateTime($t2);
$d1->isSameAs($d2);
// true
```

### Today's date

```
use MadisonSolutions\JustDate\JustDate;

// The current date, in the local timezone
$today = Date::today();

// What date is is right now in Denver?
$today_in_denver = Date::today(new DateTimeZone('America/Denver'));
```

### Traversing the calendar

```
use MadisonSolutions\JustDate\JustDate;

$d1 = new JustDate(2019, 04, 21);

$d2 = $d1->nextDay();
(string) $d2;
// 2019-04-22

```

Note JustDate objects are **immutable** - `nextDay()` and all other similar methods return new instances of JustDate!

```
(string) $d1->prevDay();
// 2019-04-20
(string) $d1->addDays(3);
// 2019-04-24

(string) $d1->startOfMonth();
// 2019-04-01
(string) $d1->endOfMonth();
// 2019-04-30

// What day was it on this day 2 years ago?
(new JustDate($d1->year - 2, $d1->month, $d1->day))->format('l');
// Friday

// Find the previous Wednesday
$date = $d1;
while ($date->day_of_week != 3) {
    $date = $date->prevDay();
}
$date->format('D F jS Y');
// Wed April 17th 2019

// How many days is it until the start of the next month?
JustDate::spanDays($d1, $d1->endOfMonth()->nextDay())
// 10
```

### Date Ranges

```
use MadisonSolutions\JustDate\DateRange;
use MadisonSolutions\JustDate\JustDate;

$start = new JustDate(2019, 04, 21);
$end = $d1->addDays(4);

$range = new DateRange($start, $end);
(string) $range;
// 2019-04-21 to 2019-04-25

(string) $range->start;
// 2019-04-21
(string) $range->end;
// 2019-04-25
$range->span;
// 4

$range->includes(new JustDate(2019, 04, 22));
// true

$range2 = DateRange::fromYmd('2019-04-18', '2019-04-23');
(string) DateRange::intersection($range, $range2);
// 2019-04-21 to 2019-04-23

$range3 = DateRange::fromYmd('2019-04-18', '2019-04-19');
DateRange::intersection($range, $range3);
// null

foreach ($range->each() as $date) {
    echo $date->format('d') . "\n";
}
// 21
// 22
// 23
// 24
// 25


try {
    $illegal_range = DateRange::fromYmd('2019-04-22', '2019-04-20');
} catch (\InvalidArgumentException $e) {
    // Won't let you create a range with end before start
}

```
