<?php

namespace Burntromi\ExceptionGenerator\BreakListener;

use Burntromi\ExceptionGenerator\Event\FileEvent;

class RootDirectoryListener extends AbstractBreakListener implements BreakListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBreak(FileEvent $event)
    {
        $dirname = $event->getDirname();
        if (DIRECTORY_SEPARATOR === $dirname // on Unix systems we loop until we reach '/'
            || preg_match('#^[a-zA-Z]+:\\\\$#', $dirname) // on Windows we match against 'x:\\'
            || $dirname === 'vfs://'
        ) {
            $event->stopPropagation();
        }
    }
}
