<?php

namespace Burntromi\ExceptionGenerator\DirLoopListener;

use Burntromi\ExceptionGenerator\Event\FileEvent;

class ExceptionDirListener extends AbstractDirLoopListener implements DirLoopListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function onDir(FileEvent $event)
    {
        if ($event->getBasename() === 'Exception' && $event->isDir()) {
            $event->setParentExceptionDir();
            $event->stopPropagation();
        }
    }
}
