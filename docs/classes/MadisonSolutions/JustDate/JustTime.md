***

# JustTime

Class JustTime

Class representing a time of day, with no date or timezone information

* Full name: `\MadisonSolutions\JustDate\JustTime`
* This class implements:
[`\JsonSerializable`](../../JsonSerializable.md)



## Properties


### since_midnight

The number of seconds from midnight to this time

```php
public int $since_midnight
```






***

### hours

Hours, from 0 to 23, as an integer

```php
public int $hours
```






***

### minutes

Minutes from 0 to 59, as an integer

```php
public int $minutes
```






***

### seconds

Seconds, from 0 to 59, as an integer

```php
public int $seconds
```






***

### _date

DateTime object created and used internally when required for formatting

```php
protected ?\DateTime $_date
```






***

## Methods


### make

Create a new JustTime instance from hours, minutes and seconds

```php
public static make(int $hours, int $minutes, int $seconds): \MadisonSolutions\JustDate\JustTime
```

Note that once created, the JustTime is immutable, there's no way to alter the internal date.
It is possible to supply numerical values which are outside of the normal ranges and
the internal date value will be adjusted to correspond.
eg supplying 10:65:00 will result in 11:05:00
eg supplying 26:-10:00 will result in 01:50:00

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$hours` | **int** | The hours (0 - 23) |
| `$minutes` | **int** | The minutes (0 - 59) |
| `$seconds` | **int** | The seconds (0 - 59) |




***

### fromSecondsSinceMidnight

Create a new JustTime instance from the total number of seconds since midnight

```php
public static fromSecondsSinceMidnight(int $seconds_since_midnight): \MadisonSolutions\JustDate\JustTime
```

Note the hours will wrap around midnight if the total number of seconds is more than a day.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds_since_midnight` | **int** | The total number of seconds since midnight |


**Return Value:**

The new JustTime instance



***

### fromDateTime

Create a new JustTime object from a DateTime object

```php
public static fromDateTime(\DateTime $date): \MadisonSolutions\JustDate\JustTime
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$date` | **\DateTime** | The DateTime object (remains unchanged) |


**Return Value:**

The new JustTime instance



***

### now

Get the current time

```php
public static now(?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\JustTime
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timezone` | **?\DateTimeZone** | Optional timezone - if specified the time will be whatever the time is right now in the specified timezone |


**Return Value:**

The new JustTime instance



***

### fromTimestamp

Get the time at the specified timestamp

```php
public static fromTimestamp(int $timestamp, ?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\JustTime
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timestamp` | **int** |  |
| `$timezone` | **?\DateTimeZone** | Optional timezone - if specified the time will be whatever the time is in the specified timezone at the specified timestamp |


**Return Value:**

The new JustTime instance



***

### fromHis

Create a new JustTime object from a string in H:i:s format

```php
public static fromHis(string $his): \MadisonSolutions\JustDate\JustTime
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$his` | **string** | The date in H:i:s format, eg &#039;14:35:02&#039; (note seconds can be omitted eg &#039;14:35&#039;) |


**Return Value:**

The new JustTime instance



***

### parseHis

Get hours minutes and seconds integers from a string in H:i:s format, if valid

```php
public static parseHis(string $his): array{0: int, 1: int, 2: int}
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$his` | **string** | The date in H:i:s format, eg &#039;14:35&#039; (note seconds can be omitted eg &#039;14:35&#039;) |


**Return Value:**

Array containing integers [year, month, day]



***

### earliest

Return the earliest of a set of times

```php
public static earliest(\MadisonSolutions\JustDate\JustTime $first, \MadisonSolutions\JustDate\JustTime $others): \MadisonSolutions\JustDate\JustTime
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$first` | **\MadisonSolutions\JustDate\JustTime** |  |
| `$others` | **\MadisonSolutions\JustDate\JustTime** |  |


**Return Value:**

The earliest time from $first and $others



***

### latest

Return the latest of a set of times

```php
public static latest(\MadisonSolutions\JustDate\JustTime $first, \MadisonSolutions\JustDate\JustTime $others): \MadisonSolutions\JustDate\JustTime
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$first` | **\MadisonSolutions\JustDate\JustTime** |  |
| `$others` | **\MadisonSolutions\JustDate\JustTime** |  |


**Return Value:**

The latest time from $first and $others



***

### quotientAndRemainder

Return the quotient and remainder when dividing integer $a by integer $b

```php
public static quotientAndRemainder(int $a, int $b): array{0: int, 1: int}
```

This differs from the PHP intdiv() function by always returning a non-negative remainder
Eg quotientAndRemainder(-10, 60) returns quotient -1 and remainder 50
This makes it suitable for 'clock' calculations (-10 minutes is equivalent to 50 minutes from the previous hour)

* This method is **static**.


* **Warning:** this method is **deprecated**. This means that this method will likely be removed in a future version.



**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$a` | **int** | the dividend |
| `$b` | **int** | the divisor |


**Return Value:**

Returns an array [quotient, remainder]



***

### split

Get the hours, minutes and seconds given the total number of seconds since midnight

```php
public static split(int $seconds_since_midnight): array{0: int, 1: int, 2: int}
```

Note the hours will wrap around midnight if the total number of seconds is more than a day.
The hours returned will always be in the interval 0-23.
The return value will be an array of integers [0 => hours, 1 => minutes, 2 => seconds]

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds_since_midnight` | **int** | The total number of seconds since midnight |


**Return Value:**

The number of hours, minutes and seconds



***

### __construct

JustTime constructor.

```php
protected __construct(int $seconds_since_midnight): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds_since_midnight` | **int** |  |




***

### getInternalDateTime

Get the internal DateTime object for 00:00 on this date (UTC)
Creates the DateTime object if it doesn't already exists

```php
protected getInternalDateTime(): \DateTime
```











***

### __toString

Standard string representation is H:i:s format

```php
public __toString(): string
```











***

### format

Create a string representation of the time, with the given format

```php
public format(string $format = &#039;H:i:s&#039;): string
```

Note that any date values which are requested in the format will have values from the Unix epoch - Jan 1st 1970






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$format` | **string** | The format, as per PHP&#039;s date() function |


**Return Value:**

The formatted string



***

### addTime

Add the specified number of hours, minutes and seconds to this time, and return a new JustTime object for the result

```php
public addTime(int $hours, int $minutes, int $seconds): \MadisonSolutions\JustDate\JustTime
```

Note values will wrap around midnight. Eg if you add 2 hours to 23:30:00 you'll get 01:30:00.
(This implies that sometimes adding positive values can lead to a time which is considered 'before' the original)
Note any of the values can be negative to subtract that amount of time instead of adding






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$hours` | **int** | The number of hours to add |
| `$minutes` | **int** | The number of minutes to add |
| `$seconds` | **int** | The number of seconds to add |


**Return Value:**

The new JustTime object



***

### isSameAs

Test whether a JustTime object refers to the same time as this one

```php
public isSameAs(\MadisonSolutions\JustDate\JustTime $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\MadisonSolutions\JustDate\JustTime** |  |


**Return Value:**

True if $other is the same time



***

### isBefore

Test whether a JustTime object refers to a time before this one

```php
public isBefore(\MadisonSolutions\JustDate\JustTime $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\MadisonSolutions\JustDate\JustTime** |  |


**Return Value:**

True if $other is before this time



***

### isBeforeOrSameAs

Test whether a JustTime object refers to a time before or equal to this one

```php
public isBeforeOrSameAs(\MadisonSolutions\JustDate\JustTime $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\MadisonSolutions\JustDate\JustTime** |  |


**Return Value:**

True if $other is before or the same as this date



***

### isAfter

Test whether a JustTime object refers to a time after this one

```php
public isAfter(\MadisonSolutions\JustDate\JustTime $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\MadisonSolutions\JustDate\JustTime** |  |


**Return Value:**

True if $other is after this date



***

### isAfterOrSameAs

Test whether a JustTime object refers to a time after or equal to this one

```php
public isAfterOrSameAs(\MadisonSolutions\JustDate\JustTime $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\MadisonSolutions\JustDate\JustTime** |  |


**Return Value:**

True if $other is after or the same as this time



***

### round

Round a time to a given interval

```php
public round(int $interval_seconds): \MadisonSolutions\JustDate\JustTime
```

For example to round 09:47 to the nearest 15 minutes:
$time = (JustTime::make(9, 47))->round(15 * 60); // 09:45






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$interval_seconds` | **int** | The length of the interval to round to, in seconds |


**Return Value:**

A new JustTime instance with the rounded time



***

### __serialize

Serialize

```php
public __serialize(): array{since_midnight: int}
```

The integer since_midnight completely defines a JustTime object, so it is sufficient for serialization









***

### __unserialize

Unserialize

```php
public __unserialize(array{since_midnight: int} $data): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **array{since_midnight: int}** |  |




***

### jsonSerialize

Json serialize to the H:i:s string

```php
public jsonSerialize(): string
```











***


***
> Automatically generated from source code comments on 2023-11-20 using [phpDocumentor](http://www.phpdoc.org/) and [saggre/phpdocumentor-markdown](https://github.com/Saggre/phpDocumentor-markdown)
