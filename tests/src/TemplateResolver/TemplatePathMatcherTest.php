<?php

namespace Burntromi\ExceptionGenerator\TemplateResolver;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher
 */
final class TemplatePathMatcherTest extends TestCase
{
    /**
     * @var TemplatePathMatcher
     */
    private $object;

    /**
     * @var string
     */
    private $configPath;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        vfsStream::setup('root', null, array(
            'home' => array(
                '.exception-generator.json' => 'empty yet'
            ),
            'currentdir' => array('test' => array()),
            'expectedpath' => array(
                'exception.phtml' => ''
            )
        ));

        $this->object = new TemplatePathMatcher(
            vfsStream::url('root/currentdir/test/bar'),
            vfsStream::url('root/home')
        );

        $this->configPath = vfsStream::url('root/home/.exception-generator.json');
    }

    /**
     * @covers ::match
     * @covers ::getPaths
     * @covers ::filterMatchingPaths
     * @covers ::getMostRelatedPath
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::__construct
     */
    public function testMatchProjectPaths()
    {
        file_put_contents($this->configPath, json_encode(array(
            'templatepath' => array(
                'projects' => array(
                    '/test/foo/bar' => '/bar/foo',
                    vfsStream::url('root/currentdir') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root/currentdir/test/Bar') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root/currentdir/test/bar') => vfsStream::url('root/expectedpath')
                )
            )
        )));

        $this->assertSame(vfsStream::url('root/expectedpath'), $this->object->match('exception.phtml'));
    }

    /**
     * @covers ::getPaths
     * @covers ::getMostRelatedPath
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::match
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::filterMatchingPaths
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::__construct
     */
    public function testMatchGlobalTemplatePath()
    {
        file_put_contents($this->configPath, json_encode(array(
            'templatepath' => array(
                'projects' => array(
                    '/test/foo/bar' => '/bar/foo',
                    vfsStream::url('root/currentdir') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root/currentdir/test/Bar') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root/currentdir/test/bar') => vfsStream::url('root/unexpectedpath')
                ),
                'global' => vfsStream::url('root/expectedpath')
            )
        )));

        $this->assertSame(vfsStream::url('root/expectedpath'), $this->object->match('exception.phtml'));
    }

    /**
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::match
     * @covers ::getPaths
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::filterMatchingPaths
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::getMostRelatedPath
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::__construct
     */
    public function testMatchGlobalPathAndProjectsPathDoesntMatch()
    {
        file_put_contents($this->configPath, json_encode(array(
            'templatepath' => array(
                'projects' => array(
                    '/test/foo/bar' => '/bar/foo',
                    vfsStream::url('root/currentdir') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root/currentdir/test/Bar') => vfsStream::url('root/unexpectedpath'),
                    vfsStream::url('root/currentdir/test/bar') => vfsStream::url('root/unexpectedpath')
                ),
                'global' => vfsStream::url('root/unexpectedpath')
            )
        )));

        $this->assertFalse($this->object->match('exception.phtml'));
    }

    /**
     * @covers ::match
     * @covers ::getPaths
     * @uses Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher::__construct
     */
    public function testMatchTemplatePathIsNotConfigured()
    {
        file_put_contents($this->configPath, json_encode(array()));
        $this->assertFalse($this->object->match('exception.phtml'));

        file_put_contents($this->configPath, json_encode(array('templatepath' => null)));
        $this->assertFalse($this->object->match('exception.phtml'));
    }

    /**
     * @covers ::__construct
     * @covers ::match
     */
    public function testMatchConfigFileIsntReadable()
    {
        unlink($this->configPath);

        $this->assertFalse($this->object->match('not interesting'));
    }

    /**
     * @covers ::__construct
     * @covers ::match
     * @expectedException \Burntromi\ExceptionGenerator\Exception\RuntimeException
     */
    public function testMatchConfigurationFileIsBroken()
    {
        $this->object->match('foobar');
    }

    /**
     * @covers ::__construct
     * @covers ::match
     * @expectedException \Burntromi\ExceptionGenerator\Exception\RuntimeException
     */
    public function testMatchConfigurationDoesntReturnArray()
    {
        file_put_contents($this->configPath, 'null');
        $this->object->match('foobar');
    }
}
