<?php

namespace Burntromi\ExceptionGenerator\FileLoopListener;

use Burntromi\ExceptionGenerator\Event\FileEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface FileLoopListenerInterface extends EventSubscriberInterface
{
    /**
     * File was found.
     *
     * Resolver listener must implement this interface.
     *
     * @param \Burntromi\ExceptionGenerator\Event\FileEvent $event
     */
    public function onFile(FileEvent $event);
}
