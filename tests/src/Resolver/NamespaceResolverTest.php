<?php

namespace Burntromi\ExceptionGenerator\Resolver;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Resolver\NamespaceResolver
 */
final class NamespaceResolverTest extends TestCase
{
    /**
     * @var NamespaceResolver
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new NamespaceResolver;
        vfsStream::setup('src');
    }

    /**
     * @covers ::resolve
     * @dataProvider provideTestPHPClasses
     */
    public function testResolve($source, $namespace)
    {
        $path = vfsStream::url('src/test.php');
        file_put_contents($path, $source);
        $this->assertSame($namespace, $this->object->resolve($path, array()));
    }

    /**
     * @covers ::resolve
     */
    public function testResolveWithLoopedDirecotries()
    {
        $path = vfsStream::url('src/test.php');
        file_put_contents($path, '<?php namespace \Test\Foo\Bar;');
        $this->assertSame(
            'Test\Foo\Bar\Test\Subpath',
            $this->object->resolve($path, array('Subpath', 'Test'))
        );
    }

    /**
     * @return array
     */
    public function provideTestPHPClasses()
    {
        return array(
            array(
                'source'    => '',
                'namespace' => false,
            ),
            array(
                'source'    => '<?php class Foo {}',
                'namespace' => false,
            ),
            array(
                'source'    => '<?php',
                'namespace' => false,
            ),
            array(
                'source'    => '<?php namespace \Test\Foo\Bar;',
                'namespace' => 'Test\Foo\Bar',
            ),
            array(
                'source'    => '<?php namespace  Test\Bar\Baz;',
                'namespace' => 'Test\Bar\Baz',
            ),
            array(
                'source'    => "<?php namespace\nTest\Burntomi;",
                'namespace' => 'Test\Burntomi',
            ),
            array(
                'source'    => "<?php namespace Test\Fabian\n;",
                'namespace' => 'Test\Fabian',
            ),
            array(
                'source'    => "<?php namespace Test\SemiMissing\n\nclass Foo{}",
                'namespace' => false,
            ),
            array(
                'source'    => "<?php namespace Test\ Fabian\n;",
                'namespace' => 'Test\Fabian',
            ),
            array(
                'source'    => "<?php ; namespace Test\ Fabian\n;",
                'namespace' => 'Test\Fabian',
            ),
            array(
                'source'    => "<?php ; namespace ;",
                'namespace' => '',
            ),
            array(
                'source'    => "<?php ; namespace Test\\Fabian\n;",
                'namespace' => 'Test\\Fabian',
            ),
            array(
                'source'    => "<?php ; namespace Test\F\n;",
                'namespace' => 'Test\\F',
            ),
            array(
                'source'    => "<?php namespace Test\F\n use Symfony\Component\Console\Command\Command
                                        use Symfony\Component\Console\Input\InputArgument
                                        protected function configure()\n{
                                        const T_WHITESPACE   = T_WHITESPACE}",
                'namespace' => false,
            )
        );
    }

    /**
     * @covers ::resolve
     * @expectedException Burntromi\ExceptionGenerator\Exception\RuntimeException
     * @expectedExceptionMessage PHP file "/phpunit-missing" isn't readable
     */
    public function testResolveFileDoesNotExist()
    {
        $this->object->resolve('/phpunit-missing', array());
    }

    /**
     * @covers ::resolve
     * @requires PHP 5.4
     * @expectedException Burntromi\ExceptionGenerator\Exception\RuntimeException
     * @expectedExceptionMessage PHP file "vfs://src/test.php" isn't readable
     */
    public function testResolveFileIsNotReadable()
    {

        $path = vfsStream::url('src/test.php');
        file_put_contents($path, '<?php');
        chmod($path, 0000);
        $this->object->resolve($path, array());
    }
}
