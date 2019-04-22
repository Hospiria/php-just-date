<?php

$utc = new DateTimeZone('UTC');
$uk = new DateTimeZone('Europe/London');

/*
$d1 = new DateTime('2019-04-21');
print_r($d1);

$d2 = new DateTime('2019-04-21', new DateTimeZone('UTC'));
print_r($d2);

print_r($d2->getTimestamp() - $d1->getTimestamp());

//$d = new DateTime();
//print_r($d);
*/

/*
$d1 = new DateTime('now', $utc);
$d2 = new DateTime('now', $uk);

print_r($d1);
print_r($d2);
*/

/*
$d1 = new DateTime(null, $utc);
$d2 = (clone $d1)->setDate(2019, 04, 62)->setTime(0, 0, 0, 0);
print_r($d1);
print_r($d2);
print_r($d1 === $d2 ? 'same' : 'not');
*/

$d1 = new DateTime('now', null);
print_r($d1);
