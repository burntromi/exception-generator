<?php

namespace Burntromi\ExceptionGenerator\DirLoopListener;

abstract class AbstractDirLoopListener implements DirLoopListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'dir.loop' => array('onDir', 0),
        );
    }
}
