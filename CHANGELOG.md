# Changelog

## [2.1.0]

### Added
 - Added `fromStartAndDuration()`, `fromEndAndDuration()`, `currentMonth()`, `currentWeek()` and `currentYear()` static methods to DateRange
 - Added `numDaysUntil()` and `numDaysSince()` methods to DayOfWeek
 - Added `startOfWeek()` and `endOfWeek()` methods to JustDate
 - Added `compare()` static method to JustDate
 - Added `contains()` and `isSameAs()` methods to DateSet and MutableDateSet (and anything extending BaseDateSet)



## [2.0.0]

See the [migration guide](migration.md) for help migrating from version 1 to version 2.

### Breaking Changes
 - Minimum PHP version required is now 8.1
 - JustDate, JustTime, DateRange constructors are now protected methods, so you can't directly call `new JustDate()` - use `JustDate::make` or `JustTime::make` or `DateRange::make` instead.
 - Changes to serialize/unserialize mechanism - you cannot unserialize using v2 from strings serialized using v1.  None of the classes now implement the `/Serializable` interface (so do not have the `serialize()` `unserialize()` methods), instead the new `__serialize()` and `__unserialize()` magic methods (introduced in PHP7.4) are now used.
 - `JustDate::spanDays()` renamed to `JustDate::difference()`.
 - Removed `DateRange::span` and `DateRange::num_nights` (use `DateRange::inner_length` instead).
 - Removed `DateRange::num_days` (use `DateRange::outer_length` instead).
 - Serialization of all classes has changed
 - Renamed `DateRange::eachExceptEnd` to `DateRange::eachExceptLast`
 - Renamed `DateRange::iterateSubRanges` to `DateRange::eachSubRange` and changed the way options are passed to this method
 - Changed behaviour of `MutableDateSet::subtract` - now returns a new object instead of mutating the original
 - JustDate property `$day_of_week` now returns an instance of the `DayOfWeek` enum instead of an integer (get the integer with `$date->day_of_week->value`)

### Added
 - Added `epoch_day` property to JustDate and static `JustDate::fromEpochDay()` function.
 - Added `subDays()`, `subWeeks()`, `subMonths()` and `subYears()` functions to JustDate as alternatives to using a negative quantity in
the old `addDays()`, `addWeeks()`, `addMonths()` and `addYears()` methods.
 - Added `toDateTime()` method to JustDate.
 - Added `addDaysPassingTest()` and `addWorkingDays()` methods to JustDate.
 - Added `fromStartAndInnerLength` and `fromStartAndOuterLength` static methods to DateRange.
 - All iterator methods can now be run in reverse by supplying the `backwards: true` argument.
 - Added `remove()` method to MutableDateSet (which has previous mutating behaviour of `subtract()` method).

### Changed
 - Internally JustDate is based on the integer 'epoch_day' (the number of days since the Unix Epoch), instead of a native PHP DateTime object.  This should improve speed and efficiency in most situations.



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
