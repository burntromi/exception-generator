<?php

namespace Burntromi\ExceptionGenerator\IntegrationTest;

use Burntromi\ExceptionGenerator\IntegrationTest\Initializer\OptionsAwareInterface;
use Burntromi\ExceptionGenerator\IntegrationTest\Initializer\Options;

/**
 * Abstract class for context classes.
 *
 * Getters and setters that are shared between all context classes.
 */
abstract class AbstractContext implements OptionsAwareInterface
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        if (!defined('USER')) {
            define('USER', 'behat');
        }

        ini_set('date.timezone', 'UTC');
    }

    /**
     *
     * @var Options
     */
    private $options;

    /**
     *
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @param Options $options
     */
    public function setOptions(Options $options)
    {
        $this->options = $options;
    }
}
