<?php

namespace Burntromi\ExceptionGenerator\Event;

use Symfony\Component\EventDispatcher\Event;
use DirectoryIterator;

class FileEvent extends Event
{
    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * ParentExceptionDir.
     *
     * @var string
     */
    protected $parentExceptionDir = null;

    /**
     * Full file path.
     *
     * @var string
     */
    protected $file;

    /**
     * File extension.
     *
     * @var string
     */
    protected $extension;

    /**
     * basename of item.
     *
     * @var string
     */
    protected $basename;

    /**
     * dirname of item.
     *
     * @var string
     */
    protected $dirname;

    /**
     * Item is an directory.
     *
     * @var bool
     */
    protected $isDir = false;

    /**
     * Cache of looped directories.
     *
     * @var array
     */
    protected $loopedDirectories = array();

    /**
     *
     * @param DirectoryIterator $file
     */
    public function __construct(DirectoryIterator $file)
    {
        $this->file      = $file->getPathname();
        $this->extension = $this->getFileExtension($file);
        $this->basename  = $file->getBasename();
        $this->dirname   = $file->getPath();
        $this->isDir     = $file->isDir();
    }

    /**
     * Get found parentExceptionDirs
     *
     * @return string
     */
    public function getParentExceptionDir()
    {
        return $this->parentExceptionDir;
    }

    /**
     * Set found parentExceptionDirs.
    */
    public function setParentExceptionDir()
    {
        $this->parentExceptionDir = $this->dirname . '/Exception';
    }

    /**
     * Get found namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set found namespace.
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Get full filename.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get basename of item.
     *
     * @return string
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * Get dirname of item.
     *
     * @return string
     */
    public function getDirname()
    {
        return $this->dirname;
    }

    /**
     * Is item an directory.
     *
     * @return bool
     */
    public function isDir()
    {
        return $this->isDir;
    }

    /**
     * Compatiblity method for PHP versions not
     * supporting DirectoryIterator::getExtension
     *
     * @param DirectoryIterator $file
     * @return string
     */
    private function getFileExtension(DirectoryIterator $file)
    {
        return $file->getExtension();
    }

    /**
     * Get looped directories while iterating up path.
     *
     * @return array
     */
    public function getLoopedDirectories()
    {
        return $this->loopedDirectories;
    }

    /**
     * Set looped directories while iterating up path.
     *
     * @param array $loopedDirectories
     */
    public function setLoopedDirectories(array $loopedDirectories)
    {
        $this->loopedDirectories = $loopedDirectories;
    }
}
