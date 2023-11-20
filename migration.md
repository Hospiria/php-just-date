
# Migrating from version 1 to version 2

Note the minimum required PHP version is now 8.1.

Warning - the serialize/unserialize mechanism has changed between v1 and v2.  You cannot `unserialize()` using v2 from strings created with `serialize()` in v1.


### Constructor functions

In version 1 you can directly construct instances of JustDate, JustTime and JustDate (the `__construct()` methods are public). In version 2, the constructors are no longer public so JustDate, JustTime and JustDate must be created via suitable static class methods instead.  For all 3, there is new static method `make()` which takes the same areguments as the v1 `__construct()` method.

So to migrate to v2:

 - Replace every instance of `new JustDate($year, $month, $day)` with `JustDate::make($year, $month, $day)`
 - Replace every instance of `new JustTime($hours, $minutes, $seconds)` with `JustTime::make($hours, $minutes, $seconds)`
 - Replace every instance of `new DateRange($start, $end)` with `DateRange::make($start, $end)`

 Note when creating JustDate, the `fromStartAndInnerLength` method is now available which might be simpler than `make`.  For example, change from:

 ```php
// v1
$start = JustDate::today();
$end = $start->addDays(3);
$range = new DateRange($start, $end);
 ```

to:

```php
// v2
$range = DateRange::fromStartAndInnerLength(JustDate::today(), 3);
```

### Renamed properties and methods

 - The `JustDate::spanDays` static method has been renamed to `JustDate::difference`
 - The `span` and `num_nights` properties of DateRange have been renamed to `inner_length`
 - The `num_days` property of DateRange has been renamed to `outer_length`
 - The `eachExceptEnd` method of DateRange has been renamed to `eachExceptLast` (which makes the behaviour when using the `backwards` flag more intuitive)
 - The `iterateSubRanges` method of DateRange has been renamed to `eachSubRange` (for consistency with other generator methods).


### Other breaking changes

In v1, the `iterateSubRanges` method of DateRange accepted a second argument `array $opts`.  The only supported option was a boolean flag `backwards`.  In v2, in the renamed method `eachSubRange` the second argument is `bool $backwards`. So you must change things like `$range->iterateSubRanges($value_fn, ['backwards' => true])` to `$range->eachSubRange($value_fn, backwards: true)`.

In v1 the `subtract` method of MutableDateSet would mutate the original object. In v2, the `subtract` method will instead return a new object leaving the original unchanged (so behaviour is consistent with the `subtract` method in DateSet).  If you want the original, mutating behaviour, use the new `remove` method of MutableDateSet instead.

As mentioned above, the serialize/unserialize mechanism has changed between v1 and v2.  You cannot `unserialize()` using v2 from strings created with `serialize()` in v1. In particular, none of the classes now implement the `/Serializable` interface (so do not have the `serialize()` `unserialize()` methods), instead the new `__serialize()` and `__unserialize()` magic methods (introduced in PHP7.4) are now used.
