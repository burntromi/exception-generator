<?php

namespace Burntromi\ExceptionGenerator\Listener;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Burntromi\ExceptionGenerator\Event\CreateExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;

class CreateExceptionListener implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var QuestionHelper
     */
    protected $question;

    /**
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Helper\QuestionHelper $question
     */
    public function __construct(OutputInterface $output, InputInterface $input, QuestionHelper $question)
    {
        $this->output   = $output;
        $this->input    = $input;
        $this->question = $question;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'creation.skipped'  => array('onSkippedCreation'),
            'overwrite.all'     => array('onOverwriteAll'),
            'skip.all'          => array('onSkipOverwriteAll'),
            'write.file'        => array('onWriteFile'),
            'overwrite.confirm' => array('onOverwriteConfirm'),
        );
    }

    /**
     * File writing was skipped event.
     *
     * @param \Burntromi\ExceptionGenerator\Event\CreateExceptionEvent $event
     */
    public function onSkippedCreation(CreateExceptionEvent $event)
    {
        $this->output->writeln('Skipped creating "' . $event->getFileName() . '"');
    }

    /**
     * Overwriting of all files event.
     *
     * @param \Burntromi\ExceptionGenerator\Event\CreateExceptionEvent $event
     */
    public function onOverwriteAll()
    {
        $this->output->writeln('Overwriting all existing files!');
    }

    /**
     * Skip confirmation to overwrite existing files event.
     *
     * @param \Burntromi\ExceptionGenerator\Event\CreateExceptionEvent $event
     */
    public function onSkipOverwriteAll()
    {
        $this->output->writeln('Skipped overwriting all existing files.');
    }

    /**
     * Overwriting of a single file event.
     *
     * @param \Burntromi\ExceptionGenerator\Event\CreateExceptionEvent $event
     */
    public function onWriteFile(CreateExceptionEvent $event)
    {
        $message = ' "' . $event->getFileName() . '"...';
        if ($event->fileExists()) {
            $message = 'Overwriting' . $message;
        } else {
            $message = 'Writing' . $message;
        }

        $this->output->writeln($message);
    }

    /**
     * Event for asking the user of confirmation to overwrite a file
     *
     * @param \Burntromi\ExceptionGenerator\Event\CreateExceptionEvent $event
     */
    public function onOverwriteConfirm(CreateExceptionEvent $event)
    {
        $question = new ChoiceQuestion(
            'File [' . $event->getFileName() . '] already exists, overwrite?',
            array('y' => 'yes', 'n' => 'no', 'all' => 'all', 'nall' => 'nall'),
            'n'
        );

        $confirm = $this->question->ask(
            $this->input,
            $this->output,
            $question
        );

        $event->setConfirm($confirm);
    }
}
