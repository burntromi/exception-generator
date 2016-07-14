<?php

namespace Burntromi\ExceptionGenerator\Event;

use Symfony\Component\EventDispatcher\Event;

class CreateExceptionEvent extends Event
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var bool
     */
    protected $fileExists;

    /**
     * @var string
     */
    protected $confirm;

    /**
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        $this->fileName   = $fileName;
        $this->fileExists = is_file($fileName);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Get confirmation value.
     *
     * @return string
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * Set confirmation value.
     *
     * @param string $confirm
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;
    }

    /**
     * Does file exist.
     *
     * @return bool
     */
    public function fileExists()
    {
        return $this->fileExists;
    }
}
