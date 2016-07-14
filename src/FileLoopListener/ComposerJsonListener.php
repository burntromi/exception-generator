<?php

namespace Burntromi\ExceptionGenerator\FileLoopListener;

use Burntromi\ExceptionGenerator\Event\FileEvent;
use Burntromi\ExceptionGenerator\Resolver\ComposerResolver;

class ComposerJsonListener extends AbstractFileLoopListener implements FileLoopListenerInterface
{
    /**
     *
     * @var ComposerResolver
     */
    protected $composerResolver;

    /**
     *
     * @param ComposerResolver $composerResolver
     */
    public function __construct(ComposerResolver $composerResolver)
    {
        $this->composerResolver = $composerResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function onFile(FileEvent $event)
    {
        if ($event->getBasename() === 'composer.json') {
            $namespace = $this->composerResolver->resolve($event->getFile(), $event->getLoopedDirectories());

            if (false !== $namespace) {
                $event->setNamespace($namespace);
            }
        }
    }
}
