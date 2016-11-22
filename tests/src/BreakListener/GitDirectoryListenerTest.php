<?php

namespace Burntromi\ExceptionGenerator\BreakListener;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;
use DirectoryIterator;
use Burntromi\ExceptionGenerator\Event\FileEvent;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\BreakListener\GitDirectoryListener
 */
final class GitDirectoryListenerTest extends TestCase
{
    /**
     * @var GitDirectoryListener
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new GitDirectoryListener;
    }

    /**
     * @covers ::onBreak
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     */
    public function testOnBreakIsDotGit()
    {
        vfsStream::setup('test', null, array('.git' => array()));

        $directoryIterator = new DirectoryIterator(vfsStream::url('test'));
        $directoryIterator->seek(2);
        $event             = new FileEvent($directoryIterator);

        $this->object->onBreak($event);
        $this->assertTrue($event->isPropagationStopped());
    }

    /**
     * @covers ::onBreak
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     */
    public function testOnBreakIsDotGitButNoDirectory()
    {
        vfsStream::setup('test', null, array('.git' => 'is a file'));

        $directoryIterator = new DirectoryIterator(vfsStream::url('test'));
        $directoryIterator->seek(2);
        $event             = new FileEvent($directoryIterator);

        $this->object->onBreak($event);
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @covers Burntromi\ExceptionGenerator\BreakListener\AbstractBreakListener::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array('file.break' => array('onBreak')),
            $this->object->getSubscribedEvents()
        );
    }
}
