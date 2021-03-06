<?php

namespace Burntromi\ExceptionGenerator\Event;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;
use DirectoryIterator;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Event\FileEvent
 */
final class FileEventTest extends TestCase
{
    /**
     * @var FileEvent
     */
    private $object;

    /**
     * @var \DirectoryIterator
     */
    private $directoryIterator;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        vfsStream::setup('test', null, array(
            'directory' => array(),
            'file.txt'  => 'test content',
        ));
        $this->directoryIterator = new DirectoryIterator(vfsStream::url('test'));
        $this->object            = new FileEvent($this->directoryIterator->current());
    }

    /**
     * @covers ::__construct
     * @covers ::getFile
     * @covers ::getExtension
     * @covers ::getBasename
     * @covers ::isDir
     * @covers ::getFileExtension
     * @covers ::getDirname
     */
    public function testGetterHasCorrectValuesDirectory()
    {
        $this->directoryIterator->seek(2);
        $object = new FileEvent($this->directoryIterator->current());
        $this->assertSame(vfsStream::url('test/directory'), $object->getFile());
        $this->assertSame('', $object->getExtension());
        $this->assertSame('directory', $object->getBasename());
        $this->assertSame(vfsStream::url('test'), $object->getDirname());
        $this->assertTrue($object->isDir());
    }

    /**
     * @covers ::__construct
     * @covers ::getFile
     * @covers ::getExtension
     * @covers ::getBasename
     * @covers ::isDir
     * @covers ::getFileExtension
     * @covers ::getDirname
     */
    public function testGetterHasCorrectValuesFile()
    {
        $this->directoryIterator->seek(3);
        $object = new FileEvent($this->directoryIterator->current());
        $this->assertSame(vfsStream::url('test/file.txt'), $object->getFile());
        $this->assertSame('txt', $object->getExtension());
        $this->assertSame('file.txt', $object->getBasename());
        $this->assertSame(vfsStream::url('test'), $object->getDirname());
        $this->assertFalse($object->isDir());
    }

    /**
     * @covers ::getNamespace
     * @covers ::setNamespace
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent::__construct
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent::getFileExtension
     */
    public function testSetAndGetNamespace()
    {
        $this->object->setNamespace('test');
        $this->assertSame('test', $this->object->getNamespace());
    }

    /**
     * @covers ::getLoopedDirectories
     * @covers ::setLoopedDirectories
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent::__construct
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent::getFileExtension
     */
    public function testSetAndGetLoopedDirectories()
    {
        $this->object->setLoopedDirectories(array('1', '2', '3'));
        $this->assertSame(array('1', '2', '3'), $this->object->getLoopedDirectories());
    }
}
