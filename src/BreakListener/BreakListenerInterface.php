<?php

namespace Burntromi\ExceptionGenerator\BreakListener;

use Burntromi\ExceptionGenerator\Event\FileEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface BreakListenerInterface extends EventSubscriberInterface
{
    /**
     * Listener for file breaks.
     *
     * @param FileEvent $event
     */
    public function onBreak(FileEvent $event);
}
