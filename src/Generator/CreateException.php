<?php

namespace Burntromi\ExceptionGenerator\Generator;

use Burntromi\ExceptionGenerator\Generator\TemplateRenderer;
use Burntromi\ExceptionGenerator\Generator\ExceptionClassNames;
use Burntromi\ExceptionGenerator\Event\CreateExceptionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateException
{
    /**
     * @var TemplateRenderer
     */
    protected $templateRenderer;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var bool
     */
    protected $overwrite;

    /**
     * for skipping confirmation to overwrite existing files
     *
     * @var bool
     */
    protected $skipAll = false;

    /**
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TemplateRenderer $templateRenderer
     * @param bool $overwrite
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TemplateRenderer $templateRenderer,
        $overwrite = false
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->eventDispatcher  = $eventDispatcher;
        $this->overwrite        = (bool) $overwrite;
    }

    /**
     * creates the exception classes and the exception folder
     *
     * @param string $namespace
     * @param string $path
     * @param string $usePath
     */
    public function create($namespace, $path, $usePath = null)
    {
        $exceptionNames = ExceptionClassNames::getExceptionClassNames();

        //create the dir for exception classes if not already exists
        $path .= '/';
        if (!is_dir($path)) {
            mkdir($path);
        }

        if (null !== $usePath) {
            $usePath .= '\\';
        }

        if ($this->overwrite) {
            $this->eventDispatcher->dispatch('overwrite.all');
        }

        foreach ($exceptionNames as $name) {
            $fileName = $path . $name . '.php';

            if ($this->validate($fileName)) {
                $specifiedUsePath = null !== $usePath ? $usePath . $name : null;
                $content          = $this->templateRenderer->render($namespace, $specifiedUsePath, $name);
                $event            = new CreateExceptionEvent($fileName);
                $this->eventDispatcher->dispatch('write.file', $event);
                file_put_contents($fileName, $content);
            } else {
                $event = new CreateExceptionEvent($fileName);
                $this->eventDispatcher->dispatch('creation.skipped', $event);
            }
        }

        $fileName = $path . 'ExceptionInterface.php';
        if ($this->validate($fileName)) {
            $specifiedUsePath = null !== $usePath ? $usePath . 'ExceptionInterface' : null;
            $content          = $this->templateRenderer->render($namespace, $specifiedUsePath);
            $event            = new CreateExceptionEvent($fileName);
            $this->eventDispatcher->dispatch('write.file', $event);
            file_put_contents($fileName, $content);
        } else {
            $event = new CreateExceptionEvent($fileName);
            $this->eventDispatcher->dispatch('creation.skipped', $event);
        }
    }

    /**
     * Check if file exists, and if so ask for overwrite confirmation
     *
     * @param string $fileName
     * @return boolean
     */
    protected function validate($fileName)
    {
        $fileExists = is_file($fileName);

        // if user has set overwrite argument or file doesnt already exists return early
        if ($this->overwrite || !$fileExists) {
            return true;
        }
        // if user has chosen to skip overwriting all existing files, then return early
        if ($this->skipAll && $fileExists) {
            return false;
        }

        $overwrite = false;
        $confirm   = $this->confirm($fileName);
        switch ($confirm) {
            case 'all':
                $this->overwrite = true;
                $overwrite       = true;
                $this->eventDispatcher->dispatch('overwrite.all');
                break;

            case 'yes':
                $overwrite = true;
                break;

            case 'nall':
                $this->skipAll = true;
                $overwrite       = false;
                $this->eventDispatcher->dispatch('skip.all');
                break;

            default:
                break;
        }

        return $overwrite;
    }

    /**
     * Ask for user confirmation.
     *
     * @param string $fileName
     * @return string
     */
    protected function confirm($fileName)
    {
        $event = new CreateExceptionEvent($fileName);
        $this->eventDispatcher->dispatch('overwrite.confirm', $event);
        return $event->getConfirm();
    }

    /**
     * Set that create overwrites classes.
     *
     * @param bool $overwrite
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = (bool) $overwrite;
    }
}
