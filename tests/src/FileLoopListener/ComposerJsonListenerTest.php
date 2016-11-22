<?php

namespace Burntromi\ExceptionGenerator\FileLoopListener;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;
use DirectoryIterator;
use Burntromi\ExceptionGenerator\Event\FileEvent;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\FileLoopListener\ComposerJsonListener
 */
final class ComposerJsonListenerTest extends TestCase
{
    /**
     * @var ComposerJsonListener
     */
    private $object;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $composerResolver;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->composerResolver = $this->createMock('Burntromi\ExceptionGenerator\Resolver\ComposerResolver');
        $this->object           = new ComposerJsonListener($this->composerResolver);
    }

    /**
     * @covers ::onFile
     * @covers ::__construct
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     */
    public function testOnFile()
    {
        vfsStream::setup('test', null, array('composer.json' => 'composer json content'));

        $this->composerResolver->expects($this->once())
                ->method('resolve')
                ->with(
                        $this->equalTo(vfsStream::url('test/composer.json')), $this->equalTo(array())
                )
                ->will($this->returnValue('MyNamespace\\'));

        $directoryIterator = new DirectoryIterator(vfsStream::url('test'));
        $directoryIterator->seek(2);
        $event             = new FileEvent($directoryIterator);

        $this->object->onFile($event);
        $this->assertSame('MyNamespace\\', $event->getNamespace());
    }

    /**
     * @covers Burntromi\ExceptionGenerator\FileLoopListener\AbstractFileLoopListener
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\ComposerJsonListener::__construct
     */
    public function testGetSubscribedEvents()
    {
        $this->assertSame(array('file.loop'      => array('onFile', 0)), $this->object->getSubscribedEvents());
    }
}
