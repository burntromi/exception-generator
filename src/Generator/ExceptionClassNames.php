<?php

namespace Burntromi\ExceptionGenerator\Generator;

class ExceptionClassNames
{
    public static function getExceptionClassNames()
    {
         $exceptionNames = array(
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
        );

        return $exceptionNames;
    }
}
