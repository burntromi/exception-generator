<?php

namespace Burntromi\ExceptionGenerator\Event;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Event\CreateExceptionEvent
 */
final class CreateExceptionEventTest extends TestCase
{
    /**
     * @var CreateExceptionEvent
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        vfsStream::setup('test', null, array('bar' => array('foo.php' => 'test content')));
        $this->object = new CreateExceptionEvent(vfsStream::url('test/bar/foo.php'));
    }

    /**
     * @covers ::getFileName
     * @covers ::__construct
     */
    public function testGetFileName()
    {
        $this->assertSame(vfsStream::url('test/bar/foo.php'), $this->object->getFileName());
    }

    /**
     * @covers ::getConfirm
     * @covers ::setConfirm
     * @uses Burntromi\ExceptionGenerator\Event\CreateExceptionEvent::__construct
     */
    public function testSetAndGetConfirm()
    {
        $this->object->setConfirm('y');
        $this->assertSame('y', $this->object->getConfirm());
    }

    /**
     * @covers ::fileExists
     * @uses Burntromi\ExceptionGenerator\Event\CreateExceptionEvent::__construct
     */
    public function testFileExists()
    {
        $this->assertTrue($this->object->fileExists(), 'File doesn\'t exist');
    }
}
