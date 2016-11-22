<?php
namespace Burntromi\ExceptionGenerator\Generator;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Generator\ExceptionClassNames
 */
final class ExceptionClassNamesTest extends TestCase
{
    /**
     * @var ExceptionClassNames
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ExceptionClassNames;
    }

    /**
     * @covers ::getExceptionClassNames
     */
    public function testGetExceptionClassNames()
    {
        $this->assertSame(
            array(
                'BadMethodCallException',
                'DomainException',
                'InvalidArgumentException',
                'LengthException',
                'LogicException',
                'OutOfBoundsException',
                'OutOfRangeException',
                'OverflowException',
                'RangeException',
                'RuntimeException',
                'UnderflowException',
                'UnexpectedValueException'
            ),
            $this->object->getExceptionClassNames()
        );
    }
}
