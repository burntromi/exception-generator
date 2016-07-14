<?php

namespace Burntromi\ExceptionGenerator\Generator;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Burntromi\ExceptionGenerator\Resolver\NamespaceResolver;
use Burntromi\ExceptionGenerator\Resolver\ComposerResolver;
use DirectoryIterator;
use Burntromi\ExceptionGenerator\FileLoopListener\PHPFileListener;
use Burntromi\ExceptionGenerator\FileLoopListener\ComposerJsonListener;
use Burntromi\ExceptionGenerator\BreakListener\GitDirectoryListener;
use Burntromi\ExceptionGenerator\BreakListener\RootDirectoryListener;
use Burntromi\ExceptionGenerator\Event\FileEvent;

class RecursiveNamespaceResolver
{
    /**
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->registerDefaultListeners();
    }

    /**
     * Register default listeners
     */
    private function registerDefaultListeners()
    {
        $this->eventDispatcher->addSubscriber(new PHPFileListener(new NamespaceResolver()));
        $this->eventDispatcher->addSubscriber(new ComposerJsonListener(new ComposerResolver));
        $this->eventDispatcher->addSubscriber(new GitDirectoryListener());
        $this->eventDispatcher->addSubscriber(new RootDirectoryListener());
    }

    /**
     * Run application.
     *
     * @param string $path working path
     */
    public function resolveNamespace($path)
    {
        $namespace       = null;
        $eventDispatcher = $this->eventDispatcher;

        // loop as long a break listener doesn't stop propagation or we have empty directories
        // we iterate through directories up
        $loopedPaths = array();
        do {
            $directory = $this->getDirectoryContents($path);
            // loop over files/directories and check if a listener can find a namespace
            foreach ($directory as $item) {
                $namespaceEvent = new FileEvent($item);
                $namespaceEvent->setLoopedDirectories($loopedPaths);
                $eventDispatcher->dispatch('file.loop', $namespaceEvent);

                // if a listener has found a namespace and because
                // of its priority is want to cancel we break early
                if ($namespaceEvent->isPropagationStopped()) {
                    $namespace = $namespaceEvent->getNamespace();
                    break 2;
                }

                // save a possible found namespace for the next iteration
                if ($namespaceEvent->getNamespace()) {
                    $namespace = $namespaceEvent->getNamespace();
                }
            }

            // we have found a namespace, so break early
            if ($namespace) {
                break;
            }

            // check for listeners that check if the path iteration loop should be stopped
            foreach ($directory as $item) {
                $breakEvent = new FileEvent($item);
                $eventDispatcher->dispatch('file.break', $breakEvent);
                if (false !== $breakEvent->isPropagationStopped()) {
                    break 2;
                }
            }
            $loopedPaths[] = basename($path);
            $path          = dirname($path) !== 'vfs:' ? dirname($path) : 'vfs://';
            //break early cuz DirectoryIterator can't handle vfs root folder
        } while ((0 === count($directory) || !$breakEvent->isPropagationStopped()) && $path !== 'vfs://');
        return $namespace;
    }

    /**
     * Get directory contents without dot files.
     *
     * @param string $path
     * @return DirectoryIterator[]
     */
    private function getDirectoryContents($path)
    {
        $directory = new DirectoryIterator($path);
        $items     = array();
        foreach ($directory as $item) {
            if (!$item->isDot()) {
                $items[] = clone $item;
            }
        }
        return $items;
    }

    /**
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
}
