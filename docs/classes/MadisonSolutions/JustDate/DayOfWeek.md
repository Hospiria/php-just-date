***

# DayOfWeek

Enum DayOfWeek



* Full name: `\MadisonSolutions\JustDate\DayOfWeek`

## Cases


### Sunday ( = 0)




***

### Monday ( = 1)




***

### Tuesday ( = 2)




***

### Wednesday ( = 3)




***

### Thursday ( = 4)




***

### Friday ( = 5)




***

### Saturday ( = 6)




***


## Methods


### isWeekday

Is this a weekday (mon - fri)?

```php
public isWeekday(): bool
```











***

### isWeekend

Is this a weekend day (sat or sun)?

```php
public isWeekend(): bool
```











***

### addDays

Return the new DayOfWeek after adding $num days

```php
public addDays(int $num): \MadisonSolutions\JustDate\DayOfWeek
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$num` | **int** |  |




***

### subDays

Return the new DayOfWeek after subtracting $num days

```php
public subDays(int $num): \MadisonSolutions\JustDate\DayOfWeek
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$num` | **int** |  |




***

***
> Automatically generated from source code comments on 2023-11-20 using [phpDocumentor](http://www.phpdoc.org/)
