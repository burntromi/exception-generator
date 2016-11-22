<?php

namespace Burntromi\ExceptionGenerator\BreakListener;

use PHPUnit_Framework_TestCase as TestCase;
use Burntromi\ExceptionGenerator\Event\FileEvent;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\BreakListener\RootDirectoryListener
 */
final class RootDirectoryListenerTest extends TestCase
{

    /**
     * @var RootDirectoryListener
     */
    private $object;

    /**
     * @var MockDirectoryIterator
     */
    private $mockedDirectoryIterator;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object                  = new RootDirectoryListener;
        $this->mockedDirectoryIterator = $this->createMock(
            'Burntromi\ExceptionGenerator\TestHelper\MockDirectoryIterator'
        );
    }

    /**
     * @covers ::onBreak
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     */
    public function testOnBreakUnix()
    {
        $root = $this->mockedDirectoryIterator;

        $root->expects($this->once())
                ->method('getPath')
                ->willReturn('/');

        $event = new FileEvent($root);
        $this->object->onBreak($event);
        $this->assertTrue($event->isPropagationStopped());
    }

    /**
     * @covers ::onBreak
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     */
    public function testOnBreakWindows()
    {

        $root = $this->mockedDirectoryIterator;

        $root->expects($this->once())
                ->method('getPath')
                ->willReturn('c:\\');

        $event = new FileEvent($root);
        $this->object->onBreak($event);
        $this->assertTrue($event->isPropagationStopped());
    }
}
