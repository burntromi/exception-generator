<?php

namespace Burntromi\ExceptionGenerator\DirLoopListener;

use Burntromi\ExceptionGenerator\Event\FileEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface DirLoopListenerInterface extends EventSubscriberInterface
{
    /**
     * File was found.
     *
     * Resolver listener must implement this interface.
     *
     * @param FileEvent $event
     */
    public function onDir(FileEvent $event);
}
