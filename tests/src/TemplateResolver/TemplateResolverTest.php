<?php

namespace Burntromi\ExceptionGenerator\TemplateResolver;

use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\TemplateResolver\TemplateResolver
 */
final class TemplateResolverTest extends TestCase
{
    /**
     * @var TemplateResolver
     */
    private $object;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $templatePathMatcher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        vfsStream::setup('root', null, array('home' => array()));

        $this->templatePathMatcher = $this->createMock(
            'Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher'
        );

        $this->object = new TemplateResolver(vfsStream::url('root/home'), $this->templatePathMatcher);
    }

    /**
     * @covers ::resolve
     * @covers ::getTemplatePath
     * @covers ::__construct
     */
    public function testResolveTemplateExistsInGivenPath()
    {
        file_put_contents(vfsStream::url('root/home/exception.phtml'), '');
        $this->assertSame(vfsStream::url('root/home/exception.phtml'), $this->object->resolve('exception.phtml'));
    }

    /**
     * @covers ::resolve
     * @covers ::getTemplatePath
     * @covers ::__construct
     */
    public function testResolveTemplateMatcherReturnsPath()
    {
        $this->templatePathMatcher->expects($this->once())
            ->method('match')
            ->will($this->returnValue('/test'));

        $this->assertSame('/test/exception.phtml', $this->object->resolve('exception.phtml'));
    }

    /**
     * @covers ::resolve
     * @covers ::getTemplatePath
     * @covers ::__construct
     */
    public function testResolveReturnsDefaultPath()
    {
        $this->templatePathMatcher->expects($this->once())
            ->method('match')
            ->will($this->returnValue(false));

        $this->assertSame(
            realpath(__DIR__ . '/../../../templates/exception.phtml'),
            $this->object->resolve('exception.phtml')
        );
    }
}
