<?php

namespace Burntromi\ExceptionGenerator\FileLoopListener;

use Burntromi\ExceptionGenerator\Event\FileEvent;
use Burntromi\ExceptionGenerator\Resolver\NamespaceResolver;

class PHPFileListener extends AbstractFileLoopListener implements FileLoopListenerInterface
{
    /**
     *
     * @var NamespaceResolver
     */
    protected $namespaceResolver;

    /**
     *
     * @param NamespaceResolver $namespaceResolver
     */
    public function __construct(NamespaceResolver $namespaceResolver)
    {
        $this->namespaceResolver = $namespaceResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function onFile(FileEvent $event)
    {
        if ($event->getExtension() === 'php') {
            $namespace = $this->namespaceResolver->resolve($event->getFile(), $event->getLoopedDirectories());

            if (false !== $namespace) {
                $event->stopPropagation();
                $event->setNamespace($namespace);
            }
        }
    }
}
