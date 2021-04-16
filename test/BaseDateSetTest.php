<?php /** @noinspection DuplicatedCode */

use MadisonSolutions\JustDate\BaseDateSet;
use MadisonSolutions\JustDate\DateRange;
use MadisonSolutions\JustDate\JustDate;
use MadisonSolutions\JustDate\DateSet;
use MadisonSolutions\JustDate\MutableDateSet;
use PHPUnit\Framework\TestCase;

class BaseDateSetTest extends TestCase
{
    public function testSerialization() {
        $tests = [
            [
                [],
                '',
                [],
            ],
            [
                [JustDate::fromYmd('2021-04-15')],
                '2021-04-15',
                [['start' => '2021-04-15', 'end' => '2021-04-15']],
            ],
            [
                [DateRange::fromYmd('2021-04-15', '2021-04-20')],
                '2021-04-15 to 2021-04-20',
                [['start' => '2021-04-15', 'end' => '2021-04-20']],
            ],
            [
                [DateRange::fromYmd('2021-02-01', '2021-02-15'), JustDate::fromYmd('2021-04-04'), DateRange::fromYmd('2021-05-20', '2021-05-22')],
                '2021-02-01 to 2021-02-15, 2021-04-04, 2021-05-20 to 2021-05-22',
                [
                    ['start' => '2021-02-01', 'end' => '2021-02-15'],
                    ['start' => '2021-04-04', 'end' => '2021-04-04'],
                    ['start' => '2021-05-20', 'end' => '2021-05-22'],
                ],
            ]
        ];

        foreach ($tests as [$args, $expected_string, $expected_json_obj]) {
            $set = new DateSet(...$args);
            $serialised = serialize($set);
            $restored = unserialize($serialised);
            $this->assertInstanceOf(DateSet::class, $restored);
            $this->assertEquals($expected_string, (string) $set);
            $this->assertEquals($expected_string, (string) $restored);
            $json = json_encode($set);
            $this->assertEquals(json_encode($expected_json_obj), $json);

            $set = new MutableDateSet(...$args);
            $serialised = serialize($set);
            $restored = unserialize($serialised);
            $this->assertInstanceOf(MutableDateSet::class, $restored);
            $this->assertEquals($expected_string, (string) $set);
            $this->assertEquals($expected_string, (string) $restored);
            $json = json_encode($set);
            $this->assertEquals(json_encode($expected_json_obj), $json);
        }
    }

    public function testGenerators()
    {
        $tests = [
            [
                [],
                [],
                [],
            ],
            [
                [JustDate::fromYmd('2021-04-15')],
                ['2021-04-15'],
                ['2021-04-15 to 2021-04-15'],
            ],
            [
                [DateRange::fromYmd('2021-04-15', '2021-04-17')],
                ['2021-04-15', '2021-04-16', '2021-04-17'],
                ['2021-04-15 to 2021-04-17'],
            ],
            [
                [DateRange::fromYmd('2021-02-01', '2021-02-03'), JustDate::fromYmd('2021-02-08'), DateRange::fromYmd('2021-02-10', '2021-02-11')],
                ['2021-02-01', '2021-02-02', '2021-02-03', '2021-02-08', '2021-02-10', '2021-02-11'],
                ['2021-02-01 to 2021-02-03', '2021-02-08 to 2021-02-08', '2021-02-10 to 2021-02-11'],
            ],
        ];

        foreach ($tests as [$args, $expected_dates, $expected_ranges]) {
            foreach ([new DateSet(...$args), new MutableDateSet(...$args)] as $set) {
                /**
                 * @var BaseDateSet $set
                 */
                $dates = [];
                foreach ($set->eachDate() as $date) {
                    $dates[] = (string)$date;
                }
                $this->assertEquals($expected_dates, $dates);
                $ranges = [];
                foreach ($set->eachRange() as $range) {
                    $ranges[] = (string)$range;
                }
                $this->assertEquals($expected_ranges, $ranges);
            }
        }
    }
}