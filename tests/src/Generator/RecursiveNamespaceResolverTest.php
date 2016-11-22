<?php

namespace Burntromi\ExceptionGenerator\Generator;

use PHPUnit_Framework_TestCase as TestCase;
use Burntromi\ExceptionGenerator\Event\FileEvent;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Generator\RecursiveNamespaceResolver
 */
final class RecursiveNamespaceResolverTest extends TestCase
{

    /**
     * @var RecursiveNamespaceResolver
     */
    private $object;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->object          = new RecursiveNamespaceResolver($this->eventDispatcher);
        vfsStream::setup('namespace-resolver', null, array('subdir' => array()));
    }

    /**
     * @covers ::resolveNamespace
     * @covers ::getDirectoryContents
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\PHPFileListener::__construct
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\ComposerJsonListener::__construct
     * @uses Burntromi\ExceptionGenerator\Generator\RecursiveNamespaceResolver::__construct
     * @uses Burntromi\ExceptionGenerator\Generator\RecursiveNamespaceResolver::registerDefaultListeners
     */
    public function testResolveNamespaceEmptyDirectory()
    {
        $this->eventDispatcher->expects($this->any())
            ->method('dispatch')
            ->will($this->returnCallback(function ($eventName, FileEvent $event) {
                if ($eventName === 'file.break') {
                    $event->stopPropagation();
                }
            }));
        $this->assertNull($this->object->resolveNamespace(vfsStream::url('namespace-resolver/subdir')));
    }

    /**
     * @covers ::resolveNamespace
     * @covers ::getDirectoryContents
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\PHPFileListener::__construct
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\ComposerJsonListener::__construct
     * @uses Burntromi\ExceptionGenerator\Generator\RecursiveNamespaceResolver::__construct
     * @uses Burntromi\ExceptionGenerator\Generator\RecursiveNamespaceResolver::registerDefaultListeners
     */
    public function testResolveNamespaceFoundNamespaceByAListener()
    {
        $this->eventDispatcher->expects($this->any())
            ->method('dispatch')
            ->will($this->returnCallback(function ($eventName, FileEvent $event) {
                if ($eventName === 'file.loop') {
                    $event->setNamespace('MyNameSpaceTest');
                }
            }));

        $this->assertSame(
            'MyNameSpaceTest',
            $this->object->resolveNamespace(vfsStream::url('namespace-resolver/subdir'))
        );
    }

    /**
     * @covers ::resolveNamespace
     * @covers ::getDirectoryContents
     * @uses Burntromi\ExceptionGenerator\Event\FileEvent
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\PHPFileListener::__construct
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\ComposerJsonListener::__construct
     * @uses Burntromi\ExceptionGenerator\Generator\RecursiveNamespaceResolver::__construct
     * @uses Burntromi\ExceptionGenerator\Generator\RecursiveNamespaceResolver::registerDefaultListeners
     */
    public function testResolveNamespaceFoundNamespaceByAListenerWhichStoppsPropagation()
    {
        $this->eventDispatcher->expects($this->any())
                ->method('dispatch')
                ->will($this->returnCallback(function ($eventName, FileEvent $event) {
                    if ($eventName === 'file.loop') {
                        $event->stopPropagation();
                        $event->setNamespace('MyNameSpaceTest');
                    }
                }));

        $this->assertSame(
            'MyNameSpaceTest',
            $this->object->resolveNamespace(vfsStream::url('namespace-resolver/subdir'))
        );
    }

    /**
     * @coversNothing
     * @dataProvider provideTestDirectories
     * @group integration
     */
    public function testResolveNamespace($structure, $path, $expected)
    {
        $eventDispatcher = new EventDispatcher;
        $eventDispatcher->addListener('file.break', function (FileEvent $event) {
            $dirname = dirname($event->getFile());
            if (vfsStream::url('namespace-resolver') === $dirname) {
                $event->stopPropagation();
            }
        });
        $object = new RecursiveNamespaceResolver($eventDispatcher);
        vfsStream::setup('namespace-resolver', null, $structure);
        $this->assertSame($expected, $object->resolveNamespace($path));
    }

    /**
     * @return array
     */
    public function provideTestDirectories()
    {
        return array(
            array(
                'structure' => array(
                    'subdir' => array(
                        'Test.php' => '<?php namespace Foobar; class Test{}'
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/'),
                'expected'  => 'Foobar',
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'Test.php' => '<?php namespace Foobar class Test{}'
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/'),
                'expected'  => null,
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'Fail.php' => '<?php namespace Fail class Test{}',
                        'Success.php' => '<?php namespace SuccessAfterFail; class Test{}'
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/'),
                'expected'  => 'SuccessAfterFail',
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'composer.json' => '{"autoload":{"psr-4":{"Burntromi\\\\ExceptionGenerator1\\\\":"src/"}}}',
                        'Success.php' => '<?php namespace IgnoredJson; class Test{}'
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/'),
                'expected'  => 'IgnoredJson',
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'composer.json' => '{"autoloat":{"psr-4":{"Burntromi\\\\ExceptionGenerator1\\\\":"src/"}}}',
                        'Success.php' => '<?php namespace SuccessAfterFailComposer; class Test{}'
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/'),
                'expected'  => 'SuccessAfterFailComposer',
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'Test.php' => '<?php namespace Foobar; class Test{}',
                        'foobarbaz' => array('.git' => array())
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/foobarbaz'),
                'expected'  => null,
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'Test.php' => '<?php namespace Foobar; class Test{}',
                        '.git' => array()
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/'),
                'expected'  => 'Foobar',
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'composer.json' => '{"autoload":{"psr-4":{"Burntromi\\\\ExceptionGenerator1\\\\":"src/"}}}',
                        'Success.php' => '<?php namespace MissingSemicolon class Test{}'
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/'),
                'expected'  => 'Burntromi\ExceptionGenerator1',
            ),
             array(
                'structure' => array(
                    'subdir' => array(
                        'foobarbaz' => array('Test.php' =>'<?php namespace Foobar; class Test{}'),
                        array('.git' => array())
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/foobarbaz'),
                'expected'  => 'Foobar',
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'foobarbaz' => array('Test.php' =>'<?php namespace Foobar class Test{}'),
                        array('.git' => array())
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/foobarbaz'),
                'expected'  => null,
            ),
            array(
                'structure' => array(
                    'subdir' => array(
                        'Fail.php' => '<?php namespace Fail class Test{}',
                        'composer.json' => '{"autoload":{"psr-4":{"Burntromi\\\\ExceptionGenerator1\\\\":"src/"}}}',
                        'Success.php' => '<?php namespace SuccessAfterFail class Test{}'
                    )
                ),
                'path'      => vfsStream::url('namespace-resolver/subdir/'),
                'expected'  => 'Burntromi\ExceptionGenerator1',
            ),
        );
    }

    /**
     * @covers ::getEventDispatcher
     * @covers ::__construct
     * @covers ::registerDefaultListeners
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\PHPFileListener::__construct
     * @uses Burntromi\ExceptionGenerator\FileLoopListener\ComposerJsonListener::__construct
     */
    public function testRegisterDefaultListeners()
    {
        $eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $eventDispatcher->expects($this->at(0))
                ->method('addSubscriber')
                ->with($this->isInstanceOf('Burntromi\ExceptionGenerator\FileLoopListener\PHPFileListener'));

        $eventDispatcher->expects($this->at(1))
                ->method('addSubscriber')
                ->with($this->isInstanceOf('Burntromi\ExceptionGenerator\FileLoopListener\ComposerJsonListener'));

        $eventDispatcher->expects($this->at(2))
                ->method('addSubscriber')
                ->with($this->isInstanceOf('Burntromi\ExceptionGenerator\BreakListener\GitDirectoryListener'));

        $eventDispatcher->expects($this->at(3))
                ->method('addSubscriber')
                ->with($this->isInstanceOf('Burntromi\ExceptionGenerator\BreakListener\RootDirectoryListener'));

        $object = new RecursiveNamespaceResolver($eventDispatcher);
        $this->assertSame($eventDispatcher, $object->getEventDispatcher());
    }
}
