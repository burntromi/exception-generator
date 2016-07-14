<?php

namespace Burntromi\ExceptionGenerator\FileLoopListener;

abstract class AbstractFileLoopListener implements FileLoopListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'file.loop' => array('onFile', 0),
        );
    }
}
