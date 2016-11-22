<?php

namespace Burntromi\ExceptionGenerator\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use Burntromi\ExceptionGenerator\Event\CreateExceptionEvent;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Listener\CreateExceptionListener
 */
final class CreateExceptionListenerTest extends TestCase
{
    /**
     * @var CreateExceptionListener
     */
    private $object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $output;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $input;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $question;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->output   = $this->createMock('\Symfony\Component\Console\Output\OutputInterface');
        $this->input    = $this->createMock('\Symfony\Component\Console\Input\InputInterface');
        $this->question = $this->createMock('\Symfony\Component\Console\Helper\QuestionHelper');
        $this->object   = new CreateExceptionListener($this->output, $this->input, $this->question);
    }

    /**
     * @covers ::getSubscribedEvents
     * @uses Burntromi\ExceptionGenerator\Listener\CreateExceptionListener::__construct
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->object->getSubscribedEvents();
        $className = get_class($this->object);
        foreach ($events as $event => $listenerMethod) {
            $method = array_shift($listenerMethod);
            $this->assertTrue(
                method_exists($this->object, $method),
                "Method \"$method\" doesn't exist in class "
                . "\"$className\" but is defined as callback for event \"$event\""
            );
        }
    }

    /**
     * @covers ::onSkippedCreation
     * @covers ::__construct
     * @uses Burntromi\ExceptionGenerator\Event\CreateExceptionEvent
     */
    public function testOnSkippedCreation()
    {
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo('Skipped creating "testfilename"'));

        $event = new CreateExceptionEvent('testfilename');
        $this->object->onSkippedCreation($event);
    }

    /**
     * @covers ::onOverwriteAll
     * @covers ::__construct
     */
    public function testOnOverwriteAll()
    {
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo('Overwriting all existing files!'));

        $this->object->onOverwriteAll();
    }

    /**
     * @covers ::onWriteFile
     * @covers ::__construct
     */
    public function testOnWriteFileFileDoesntExist()
    {
        $event = $this->createMock('Burntromi\ExceptionGenerator\Event\CreateExceptionEvent');

        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo('Writing "testfilename"...'));

        $event->expects($this->once())
            ->method('getFileName')
            ->willReturn('testfilename');

        $event->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        $this->object->onWriteFile($event);
    }

    /**
     * @covers ::onWriteFile
     * @covers ::__construct
     */
    public function testOnWriteFileFileDoesExist()
    {
        $event = $this->createMock('Burntromi\ExceptionGenerator\Event\CreateExceptionEvent');

        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->equalTo('Overwriting "testfilename"...'));

        $event->expects($this->once())
            ->method('getFileName')
            ->willReturn('testfilename');

        $event->expects($this->once())
            ->method('fileExists')
            ->willReturn(true);

        $this->object->onWriteFile($event);
    }

    /**
     * @covers ::onOverwriteConfirm
     * @covers ::__construct
     * @uses Burntromi\ExceptionGenerator\Event\CreateExceptionEvent
     */
    public function testOnOverwriteConfirm()
    {
        $this->question->expects($this->once())
            ->method('ask')
            ->with(
                $this->equalTo($this->input),
                $this->equalTo($this->output),
                $this->callback(function (ChoiceQuestion $object) {
                    return strpos($object->getQuestion(), 'testfilename') !== false;
                })
            )
            ->willReturn('y');

        $event = new CreateExceptionEvent('testfilename');
        $this->object->onOverwriteConfirm($event);
        $this->assertSame('y', $event->getConfirm());
    }
}
