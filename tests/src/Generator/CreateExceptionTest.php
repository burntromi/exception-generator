<?php

namespace Burntromi\ExceptionGenerator\Generator;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;
use Burntromi\ExceptionGenerator\Event\CreateExceptionEvent;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Generator\CreateException
 */
final class CreateExceptionTest extends TestCase
{

    /**
     * @var CreateException
     */
    private $object;

    /**
     * @var $eventDispatcher
     */
    private $eventDispatcher;

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $templateRenderer;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->eventDispatcher  = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->templateRenderer = $this->createMock('Burntromi\ExceptionGenerator\Generator\TemplateRenderer');

        $this->object = new CreateException($this->eventDispatcher, $this->templateRenderer);
        vfsStream::setup('src', null, array());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::validate
     * @covers ::setOverwrite
     * @covers ::confirm
     * @uses Burntromi\ExceptionGenerator\Generator\ExceptionClassNames
     * @uses Burntromi\ExceptionGenerator\Event\CreateExceptionEvent
     * @dataProvider provideTestData
     * @param string $confirm           What confirmation string should be returned
     * @param bool   $overwrite         Set overwrite option at class
     * @param bool   $writeFiles        Write test files to emulate existing files
     * @param int    $dispatchCount     Expected count of EventDispatcher's dispatch() is called
     * @param int    $templateCount     Expected count of TemplateRenderer called
     * @param array  $expectedEvents    Events that are excepted to be called
     * @param array  $expectedFileNames Expected files name when dispatching
     * @param string $content           Expected content of generated files
     */
    public function testCreate(
        $confirm,
        $overwrite,
        $writeFiles,
        $dispatchCount,
        $templateCount,
        array $expectedEvents,
        array $expectedFileNames,
        $content = ''
    ) {
        $this->object->setOverwrite($overwrite);

        $path = vfsStream::url('src/exceptions');

        $knownClassNames = ExceptionClassNames::getExceptionClassNames();
        $expectedPassedClassNames   = $knownClassNames;
        $expectedPassedClassNames[] = null;

        $files = array(
            'BadMethodCallException.php'   => '',
            'DomainException.php'          => '',
            'InvalidArgumentException.php' => '',
            'LengthException.php'          => '',
            'LogicException.php'           => '',
            'OutOfBoundsException.php'     => '',
            'OutOfRangeException.php'      => '',
            'OverflowException.php'        => '',
            'RangeException.php'           => '',
            'RuntimeException.php'         => '',
            'UnderflowException.php'       => '',
            'UnexpectedValueException.php' => '',
            'ExceptionInterface.php'       => '',
        );

        if ($writeFiles) {
            vfsStream::create(array('exceptions' => $files));
        }

        $this->templateRenderer->expects($this->exactly($templateCount))
            ->method('render')
            ->with(
                $this->equalTo('testnamespace'),
                $this->equalTo(null),
                $this->callback(function($name) use (&$expectedPassedClassNames) {
                    $currentName = array_shift($expectedPassedClassNames);
                    return $name === $currentName;
                })
            )
            ->willReturn($content);

        $this->eventDispatcher->expects($this->exactly($dispatchCount))
            ->method('dispatch')
            ->with(
                $this->callback(function ($eventName) use($expectedEvents) {
                    return in_array($eventName, $expectedEvents);
                }),
                $this->callback(function (CreateExceptionEvent $event = null) use($path, &$expectedFileNames) {
                    if ($event === null) {
                        return true;
                    }

                    $currentFile = array_shift($expectedFileNames);

                    // Workaround for Phpunit calls callback more often then expected
                    // remove if {@link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/181} is fixed
                    if (null === $currentFile) {
                        return true;
                    }

                    $currentFileName = $path . '/' . $currentFile;
                    return $event->getFileName() === $currentFileName;
                })
            )
            ->willReturnCallback(function ($eventName, CreateExceptionEvent $event = null) use($confirm) {
                if ($eventName === 'overwrite.confirm') {
                    $event->setConfirm($confirm);
                }
            });

        $this->object->create('testnamespace', $path);
        $this->assertFileExists($path);
        $this->assertTrue(is_dir($path), 'Failed asserting that exception directory is an directory');

        foreach (ExceptionClassNames::getExceptionClassNames() as $className) {
            $fileName = $path . '/' . $className . '.php';
            $this->assertFileExists($fileName);
            $this->assertSame($content, file_get_contents($fileName));
        }
    }

    /**
     * @return array
     */
    public function provideTestData()
    {
        return array(
            array(
                'confirm'           => null,
                'overwrite'         => false,
                'writeFiles'        => false,
                'dispatchCount'     => 13,
                'templateCount'     => 13,
                'expectedEvents'    => array('write.file'),
                'expectedFileNames' => array(),
                'content'           => 'testcontent',
            ),
            array(
                'confirm'           => null,
                'overwrite'         => false,
                'writeFiles'        => true,
                'dispatchCount'     => 26,
                'templateCount'     => 0,
                'expectedEvents'    => array('overwrite.confirm', 'creation.skipped'),
                'expectedFileNames' => array(
                    'BadMethodCallException.php',
                    'BadMethodCallException.php',
                    'DomainException.php',
                    'DomainException.php',
                    'InvalidArgumentException.php',
                    'InvalidArgumentException.php',
                    'LengthException.php',
                    'LengthException.php',
                    'LogicException.php',
                    'LogicException.php',
                    'OutOfBoundsException.php',
                    'OutOfBoundsException.php',
                    'OutOfRangeException.php',
                    'OutOfRangeException.php',
                    'OverflowException.php',
                    'OverflowException.php',
                    'RangeException.php',
                    'RangeException.php',
                    'RuntimeException.php',
                    'RuntimeException.php',
                    'UnderflowException.php',
                    'UnderflowException.php',
                    'UnexpectedValueException.php',
                    'UnexpectedValueException.php',
                    'ExceptionInterface.php',
                    'ExceptionInterface.php',
                ),
                'content'           => '',
            ),
            array(
                'confirm'           => 'yes',
                'overwrite'         => false,
                'writeFiles'        => true,
                'dispatchCount'     => 26,
                'templateCount'     => 13,
                'expectedEvents'    => array('overwrite.confirm', 'write.file'),
                'expectedFileNames' => array(
                    'BadMethodCallException.php',
                    'BadMethodCallException.php',
                    'DomainException.php',
                    'DomainException.php',
                    'InvalidArgumentException.php',
                    'InvalidArgumentException.php',
                    'LengthException.php',
                    'LengthException.php',
                    'LogicException.php',
                    'LogicException.php',
                    'OutOfBoundsException.php',
                    'OutOfBoundsException.php',
                    'OutOfRangeException.php',
                    'OutOfRangeException.php',
                    'OverflowException.php',
                    'OverflowException.php',
                    'RangeException.php',
                    'RangeException.php',
                    'RuntimeException.php',
                    'RuntimeException.php',
                    'UnderflowException.php',
                    'UnderflowException.php',
                    'UnexpectedValueException.php',
                    'UnexpectedValueException.php',
                    'ExceptionInterface.php',
                    'ExceptionInterface.php',
                ),
                'content'           => 'testcontent',
            ),
            array(
                'confirm'           => null,
                'overwrite'         => true,
                'writeFiles'        => true,
                'dispatchCount'     => 14,
                'templateCount'     => 13,
                'expectedEvents'    => array('overwrite.all', 'write.file'),
                'expectedFileNames' => array(
                    'BadMethodCallException.php',
                    'DomainException.php',
                    'InvalidArgumentException.php',
                    'LengthException.php',
                    'LogicException.php',
                    'OutOfBoundsException.php',
                    'OutOfRangeException.php',
                    'OverflowException.php',
                    'RangeException.php',
                    'RuntimeException.php',
                    'UnderflowException.php',
                    'UnexpectedValueException.php',
                    'ExceptionInterface.php',
                ),
                'content'           => 'testcontent',
            ),
            array(
                'confirm'           => 'all',
                'overwrite'         => false,
                'writeFiles'        => true,
                'dispatchCount'     => 15,
                'templateCount'     => 13,
                'expectedEvents'    => array('overwrite.all', 'overwrite.confirm', 'write.file'),
                'expectedFileNames' => array(
                    'BadMethodCallException.php',
                    'BadMethodCallException.php',
                    'DomainException.php',
                    'InvalidArgumentException.php',
                    'LengthException.php',
                    'LogicException.php',
                    'OutOfBoundsException.php',
                    'OutOfRangeException.php',
                    'OverflowException.php',
                    'RangeException.php',
                    'RuntimeException.php',
                    'UnderflowException.php',
                    'UnexpectedValueException.php',
                    'ExceptionInterface.php',
                ),
                'content'           => 'testcontent',
            ),
        );
    }
}
