# Changelog

## [2.0.0]

### Breaking Changes
 - Minimum PHP version required is now 8.1
 - JustDate constructor is now a protected method, so you can't directly call `new JustDate()` - use `JustDate::make($year, $month, $day)` instead
   The old constructor functionality is now available via `JustDate::make($year, $month, $day)`.
 - `JustDate::spanDays()` renamed to `JustDate::numNights()`.
 - Removed `DateRange::span` (use `DateRange::num_nights` instead).
 - Serialization of JustDate now done via the epoch day (integer) instead of Y-m-d string.
   This means if you have a saved string serialization of a JustDate that was serialized in v1 you will not be able to deserialize it in v2.

### Added
 - Added `epoch_day` property to JustDate and static `JustDate::fromEpochDay()` function.
 - Added `subDays()`, `subWeeks()`, `subMonths()` and `subYears()` functions to JustDate as alternatives to using a negative quantity in
the old `addDays()`, `addWeeks()`, `addMonths()` and `addYears()` methods.
 - Added `toDateTime()` method to JustDate.  
 - Added `addDaysPassingTest()` and `addWorkingDays()` methods to JustDate.
 - Added `DateSet` and `MutableDate` classes.



## [1.1.4] - 2021-04-16
### Added
 - Added `isEmpty()`, `getSpanningRange()` and `window()` methods to DateSet and MutableDateSet
 - Added serialization support to DateSet and MutableDateSet

## [1.1.3] - 2021-04-15
### Added
- Added checks for specific days of the week (`JustDate::isSunday()` etc)
- Add `num_days` and `num_nights` properties to DateRange.
- Add some convenience methods `JustDate::yesterday()`, `JustDate::tommorrow()`, `JustDate::addWeeks()`,
  `JustDate::addMonths()`, `JustDate::addYears()`, `DateRange::eitherWayRound()`.
- Add functions for splitting a range into subranges using a user defined callback function
  (`DateRange::iterateSubRanges()`).
- Add 2 new concrete classes DateSet and MutableDateSet which can be used to store and manipulate arbitrary sets of
  dates without any duplications or overlaps.
DateSet and MutableDateSet both inherit from BaseDateSet, and have the normal set-related functions, eg `union()`,
  `intersection()`, `subtract()`, `contains()`.  Both can be constructed from JustDate and DateRange objects.  Main
  difference between them (obviously) is that DateSet is immutable, but MutableDateSet can be mutated.

### Changed
 - Lots of code cleanup

## [1.1.2] - 2019-06-17
### Added
- Added JustTime::fromSecondsSinceMidnight() function
- Added JustTime::split() function
- Added round() method to JustTime instances

### Deprecated
- JustTime::quotientAndRemainder() will be removed in version 1.2.0

## [1.1.1] - 2019-06-06
### Changed
- Updated docs

## [1.1.0] - 2019-06-06
### Added
- This changelog
- A new class JustTime which takes on the complementary problem of dealing with time data that does not have a date component.
