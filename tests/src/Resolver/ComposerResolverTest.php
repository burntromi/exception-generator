<?php

namespace Burntromi\ExceptionGenerator\Resolver;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Resolver\ComposerResolver
 */
final class ComposerResolverTest extends TestCase
{
    /**
     * @var ComposerResolver
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ComposerResolver;
        vfsStream::setup('src');
    }

    /**
     * @covers ::resolve
     * @dataProvider provideTestComposerJson
     */
    public function testResolve($source, $namespace)
    {
        $path = vfsStream::url('src/composer.json');
        file_put_contents($path, $source);
        $this->assertSame($namespace, $this->object->resolve($path, array()));
    }

    /**
     * @covers ::resolve
     */
    public function testResolveWithLoopedDirecotries()
    {
        $path = vfsStream::url('src/composer.json');
        file_put_contents(
            $path,
            '{"autoload":{"psr-4":{"Burntromi\\\\ExceptionGenerator\\\\":"src/"}}}'
        );
        $this->assertSame(
            "Burntromi\ExceptionGenerator\Foo\Bar",
            $this->object->resolve($path, array('Bar', 'Foo', 'ExceptionGenerator', 'Burntromi', 'src'))
        );
    }

    /**
     * @return array
     */
    public function provideTestComposerJson()
    {
        return array(
            array(
                'source'    => '{"autoload":{"psr-4":{"Burntromi\\\\ExceptionGenerator1\\\\":"src/"}}}',
                'namespace' => "Burntromi\ExceptionGenerator1",
            ),
            array(
                'source'    => '{"autoload":{"psr-0":{"Burntromi\\\\ExceptionGenerator2\\\\":"src/"}}}',
                'namespace' => "Burntromi\ExceptionGenerator2",
            ),
            array(
                'source'    => '{"autoload":{"psr-1":{"Burntromi\\\\ExceptionGenerator3\\\\":"src/"}}}',
                'namespace' => false,
            ),
            array(
                'source'    => '{"autoload":{"psr-2":{"Burntromi\\\\ExceptionGenerator4\\\\":"src/"}}}',
                'namespace' => false,
            ),
            array(
                'source'    => '{"autoloat":{"psr-2":{"Burntromi\\\\ExceptionGenerator4\\\\":"src/"}}}',
                'namespace' => false,
            ),
            array(
                'source'    => '"autoloat":{"psr-2":{"Burntromi\\\\ExceptionGenerator4\\\\":"src/"}}',
                'namespace' => false,
            ),
            array(
                'source'    => '{"autoload":{"psr-4":{"Burntromi\ExceptionGenerator1\":"src/"}}}',
                'namespace' => false,
            ),
            array(
                'source'    => '',
                'namespace' => false,
            ),
            array(
                'source'    => '{"autoload": {"psr-4": {"Burntromi\\\\ExceptionGenerator1\\\\": "src/",
                                                          "Burntromi\\\\ExceptionGenerator2\\\\": "src/"
                                                          }}}',
                'namespace' => "Burntromi\ExceptionGenerator1",
            ),
            array(
                'source'    => '{"autoload": {"psr-4": {"Burntromi\\ExceptionGenerator1\\": "src/",
                                                          "Burntromi\\\\ExceptionGenerator2\\\\": "src/"
                                                          }}}',
                'namespace' => false,
            ),
        );
    }
}
