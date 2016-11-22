<?php
namespace Burntromi\ExceptionGenerator\FileLoopListener;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;
use DirectoryIterator;
use Burntromi\ExceptionGenerator\Event\FileEvent;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\FileLoopListener\PHPFileListener
 */
final class PHPFileListenerTest extends TestCase
{
    /**
     * @var PHPFileListener
     */
    private $object;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $namespaceResolver;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->namespaceResolver = $this->createMock('Burntromi\ExceptionGenerator\Resolver\NamespaceResolver');
        $this->object = new PHPFileListener($this->namespaceResolver);
    }

    /**
     * @covers ::onFile
     * @covers ::__construct
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     */
    public function testOnFile()
    {
        vfsStream::setup('test', null, array('Test.php' => 'composer json content'));

        $this->namespaceResolver->expects($this->once())
            ->method('resolve')
            ->with(
                $this->equalTo(vfsStream::url('test/Test.php')),
                $this->equalTo(array())
            )
            ->will($this->returnValue('MyNamespace\\'));

        $directoryIterator = new DirectoryIterator(vfsStream::url('test'));
        $directoryIterator->seek(2);
        $event             = new FileEvent($directoryIterator);

        $this->object->onFile($event);
        $this->assertSame('MyNamespace\\', $event->getNamespace());
    }
}
