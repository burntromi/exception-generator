<?php

namespace Burntromi\ExceptionGenerator\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Burntromi\ExceptionGenerator\Generator\RecursiveNamespaceResolver;
use Burntromi\ExceptionGenerator\Generator\RecursiveParentExceptionResolver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Input\InputOption;
use Burntromi\ExceptionGenerator\Generator\CreateException;
use Burntromi\ExceptionGenerator\Generator\TemplateRenderer;
use Burntromi\ExceptionGenerator\Listener\CreateExceptionListener;
use Symfony\Component\Console\Question\Question;
use Burntromi\ExceptionGenerator\TemplateResolver\TemplateResolver;
use Burntromi\ExceptionGenerator\TemplateResolver\TemplatePathMatcher;

class ExceptionGeneratorCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('exception-generator')
            ->setDescription('Generates Exception Classes for php files in current dir.')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Basepath for generating exception class.'
            )
            ->addOption(
                'overwrite',
                'o',
                InputOption::VALUE_NONE,
                'Force overwriting existing exception classes.'
            )
            ->addOption(
                'template-path',
                't',
                InputOption::VALUE_REQUIRED,
                'Set path for templates you want to use.'
            )
            ->addOption(
                'no-parents',
                'p',
                InputOption::VALUE_NONE,
                'Disable searching for parent exceptions.'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('path')) {
            $path = $this->realpath($input->getArgument('path'));
        } else {
            $path = getcwd();
        }

        /* @var $questionHelper \Symfony\Component\Console\Helper\QuestionHelper */
        $questionHelper      = $this->getHelper('question');
        $eventDispatcher     = new EventDispatcher;
        $eventDispatcher->addSubscriber(new CreateExceptionListener($output, $input, $questionHelper));
        $namespaceResolver   = new RecursiveNamespaceResolver($eventDispatcher);
        $namespace           = $namespaceResolver->resolveNamespace($path);
        $templatePathMatcher = new TemplatePathMatcher($path, $this->getApplication()->getHome());

        $templatePath     = $this->realpath($input->getOption('template-path')) ? : null;
        $templateResolver = new TemplateResolver($templatePath, $templatePathMatcher);

        $exceptionTemplate = $templateResolver->resolve('exception.phtml');
        $interfaceTemplate = $templateResolver->resolve('interface.phtml');

        $useParents = $input->getOption('no-parents') ? false : true;

        $output->writeln('Using path for templates: ', OutputInterface::VERBOSITY_VERY_VERBOSE);
        $output->writeln('Exception-Path: "' . $exceptionTemplate . '"', OutputInterface::VERBOSITY_VERY_VERBOSE);
        $output->writeln('Interface-Path: "' . $interfaceTemplate . '"', OutputInterface::VERBOSITY_VERY_VERBOSE);

        $templateRenderer = new TemplateRenderer();
        $templateRenderer->addPath('exception', $exceptionTemplate);
        $templateRenderer->addPath('interface', $interfaceTemplate);

        $parentExceptionNamespace = null;

        if (false !== $useParents) {
            $parentExceptionResolver = new RecursiveParentExceptionResolver($eventDispatcher);
            $parentExceptionDirs     = $parentExceptionResolver->resolveExceptionDirs($path);
            if (is_array($parentExceptionDirs)) {
                $parentExceptionDirs = array_reverse($parentExceptionDirs);
                foreach ($parentExceptionDirs as $parentExceptionDir) {
                    $prevParentNamespace      = $parentExceptionNamespace;
                    $parentExceptionNamespace = $namespaceResolver->resolveNamespace($parentExceptionDir);

                    $output->writeln(
                        'BaseExceptionPath: ' . $parentExceptionDir,
                        OutputInterface::VERBOSITY_VERY_VERBOSE
                    );
                    $output->writeln(
                        'BaseExceptionNamespace: ' . $parentExceptionNamespace,
                        OutputInterface::VERBOSITY_VERY_VERBOSE
                    );

                    $parentExceptionCreator = new CreateException(
                        $eventDispatcher,
                        $templateRenderer,
                        false,
                        $output,
                        $input
                    );
                    $parentExceptionCreator->create(
                        $parentExceptionNamespace,
                        $parentExceptionDir,
                        $prevParentNamespace
                    );
                }
            }
        }

        if ($parentExceptionNamespace && false === $useParents ||
            ($parentExceptionNamespace && false !== $useParents)) {
            $output->writeln('BaseExceptionPath: not found/used', OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        $namespaceQuestion = new Question("Is this the correct namespace: [$namespace]?", $namespace);
        $inputNamespace    = $questionHelper->ask($input, $output, $namespaceQuestion);
        $output->writeln('Namespace set to "' . $inputNamespace . '"');

        $exceptionCreator = new CreateException(
            $eventDispatcher,
            $templateRenderer,
            $input->getOption('overwrite'),
            $output,
            $input
        );
        $exceptionCreator->create($inputNamespace, $path . '/Exception', $parentExceptionNamespace);
    }

    /**
     * Realpath.
     *
     * @param string $path
     * @return string|false
     */
    protected function realpath($path)
    {
        // extra check for virtual file system since vfsstream can't handle realpath()
        if (substr($path, 0, 6) === 'vfs://') {
            return $path;
        }

        return realpath($path);
    }
}
