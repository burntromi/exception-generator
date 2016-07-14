<?php

namespace Burntromi\ExceptionGenerator\Generator;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DirectoryIterator;
use Burntromi\ExceptionGenerator\BreakListener\GitDirectoryListener;
use Burntromi\ExceptionGenerator\BreakListener\RootDirectoryListener;
use Burntromi\ExceptionGenerator\DirLoopListener\ExceptionDirListener;
use Burntromi\ExceptionGenerator\Event\FileEvent;

class RecursiveParentExceptionResolver
{
    /**
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * provides a namespace dpending on looped folders after searching for parent exceptions, which you should use
     *
     * @var type String
     */
    protected $providedNamespace;

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
        $this->eventDispatcher->addSubscriber(new GitDirectoryListener());
        $this->eventDispatcher->addSubscriber(new RootDirectoryListener());
        $this->eventDispatcher->addSubscriber(new ExceptionDirListener());
    }

    /**
     * Returns an array containing arrays with parent exception folder and its namespace
     *
     * @param string $path working path
     */
    public function resolveExceptionDirs($path)
    {
        $exceptionDirArray = null;
        $eventDispatcher   = $this->eventDispatcher;
        $loopedPaths[]     = basename($path);
        $path              = dirname($path);
        // loop as long a break listener doesn't stop propagation or we have empty directories
        // we iterate through directories up
        do {
            $directory = $this->getDirectoryContents($path);
            // loop over files/directories and check if the listener can find an exception directory
            foreach ($directory as $item) {
                $exceptionDirectoryEvent = new FileEvent($item);
                $eventDispatcher->dispatch('dir.loop', $exceptionDirectoryEvent);
                //break early, cuz one exception directory can only appear once
                if ($exceptionDirectoryEvent->isPropagationStopped()) {
                    $exceptionDirArray[] = $exceptionDirectoryEvent->getParentExceptionDir();
                    break;
                }
            }


            // check for listeners that check if the path iteration loop should be stopped
            foreach ($directory as $item) {
                $breakEvent = new FileEvent($item);
                $eventDispatcher->dispatch('file.break', $breakEvent);
                if (false !== $breakEvent->isPropagationStopped()) {
                    break 2;
                }
            }
            $path          = dirname($path) !== 'vfs:' ? dirname($path) : 'vfs://';
            $loopedPaths[] = basename($path);
            //break early cuz DirectoryIterator can't handle vfs root folder
        } while ((0 === count($directory) || !$breakEvent->isPropagationStopped()) && $path !== 'vfs://');


        return $exceptionDirArray;
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
