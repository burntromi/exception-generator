<?php

namespace Burntromi\ExceptionGenerator\BreakListener;

abstract class AbstractBreakListener implements BreakListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'file.break' => array('onBreak')
        );
    }
}
