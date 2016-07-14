<?php

namespace Burntromi\ExceptionGenerator\IntegrationTest\Initializer;

use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Behat\Context\Context;
use Burntromi\ExceptionGenerator\IntegrationTest\Initializer\Options;
use Burntromi\ExceptionGenerator\IntegrationTest\Initializer\OptionsAwareInterface;

class ApplicationInitializer implements ContextInitializer
{

    /**
     * Options to be passed to contexts.
     *
     * @var array
     */
    protected $options;

    /**
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = new Options($options);
    }

    /**
     * Set clones options to object.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof OptionsAwareInterface) {
            $context->setOptions($this->options);
        }
    }
}
