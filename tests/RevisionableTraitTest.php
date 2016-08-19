<?php
/**
 * RevisionableModel.php
 * @package    revisionable
 * @author     raf <raftalks@gmail.com>
 * @copyright  Copyright (c) 2016, raf
 */

namespace Raftalks\Revisionable;


class RevisionableTraitTest extends \TestCase
{


    /**
     * @covers RevisionableTraitTest::getDiff
     */
    public function testGetDiff()
    {

        $mock = new \TestModel();

        $mock->setRawAttributes([
            'name' => 'Raf',
            'url' => 'http://github.com/raftalks',
            'rating' => 3
        ], true);

        $this->assertFalse($mock->isDirty());

        $mock->setAttribute('name', 'Raf Kewl');

        $this->assertTrue($mock->isDirty());

        $diff = $mock->getDiff();

        $this->assertEquals(['name' => 'Raf'], $diff['before']);
        $this->assertEquals(['name' => 'Raf Kewl'], $diff['after']);

    }
}
