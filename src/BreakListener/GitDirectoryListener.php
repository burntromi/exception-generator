<?php

namespace Burntromi\ExceptionGenerator\BreakListener;

use Burntromi\ExceptionGenerator\Event\FileEvent;

class GitDirectoryListener extends AbstractBreakListener implements BreakListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBreak(FileEvent $event)
    {
        if ($event->getBasename() === '.git' && $event->isDir()) {
            $event->stopPropagation();
        }
    }
}
