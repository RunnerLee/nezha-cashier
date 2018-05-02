<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-02
 */

namespace Runner\NezhaCashier\Testing;

use Runner\NezhaCashier\Utils\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{

    public function testOffsetExists()
    {
        $collection = new Collection([
            'a' => 'b',
        ]);

        $this->assertSame(true, $collection->offsetExists('a'));
        $this->assertSame(false, $collection->offsetExists('b'));
    }

    public function testOffsetGet()
    {
        $collection = new Collection([
            'a' => 'b',
        ]);
        $this->assertSame('b', $collection->offsetGet('a'));
    }

    public function testOffsetSet()
    {
        $collection = new Collection([
            'a' => 'b',
        ]);
        $collection->offsetSet('a', 'c');
        $this->assertSame('c', $collection->offsetGet('a'));
        $collection->offsetSet('b', 'd');
        $this->assertSame('d', $collection->offsetGet('b'));
        $this->assertSame('d', $collection->offsetGet('b'));
        $this->assertSame('c', $collection->offsetGet('a'));
    }

    public function testOffsetUnset()
    {
        $collection = new Collection([
            'a' => 'b',
        ]);
        $collection->offsetUnset('a');

        $this->assertSame(false, isset($collection->all()['a']));
    }

    public function testCount()
    {
        $collection = new Collection([
            'a' => [
                'b' => 'c',
            ],
        ]);
        $this->assertSame(1, count($collection));
    }

    public function testAll()
    {
        $collection = new Collection($data = [
            'a' => 'b',
        ]);
        $this->assertSame($data, $collection->all());
    }

    public function testGet()
    {
        $collection = new Collection($data = [
            'a' => [
                'b' => [
                    'c' => [
                        'd' => 'hello world',
                    ]
                ],
            ],
        ]);

        $this->assertSame('hello world', $collection->get('a.b.c.d'));

        try {
            $collection->get('a.b.c.e');
        } catch (\Exception $e) {
        }

        $this->assertSame($data, $collection->all());
    }


    public function testSet()
    {
        $collection = new Collection();

        $collection->set('a.b.c.d', 'hello world');

        $this->assertSame(
            [
                'a' => [
                    'b' => [
                        'c' => [
                            'd' => 'hello world',
                        ]
                    ],
                ],
            ],
            $collection->all()
        );

        $collection->set('a.b.c.d.e', 'php');

        $this->assertSame(
            [
                'a' => [
                    'b' => [
                        'c' => [
                            'd' => [
                                0 => 'hello world',
                                'e' => 'php',
                            ],
                        ]
                    ],
                ],
            ],
            $collection->all()
        );
    }

    public function testHas()
    {
        $collection = new Collection([
            'a' => [
                'b' => [
                    'c' => [
                        'd' => 'hello world',
                    ]
                ],
            ],
        ]);

        $this->assertSame(true, $collection->has('a.b.c.d'));
        $this->assertSame(false, $collection->has('a.b.c.e'));
    }
}
