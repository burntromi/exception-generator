<?php

namespace Burntromi\ExceptionGenerator\IntegrationTest\Initializer;

use Burntromi\ExceptionGenerator\IntegrationTest\Initializer\Options;

interface OptionsAwareInterface
{

    /**
     * Set options object.
     *
     * @param Options $options
     */
    public function setOptions(Options $options);

    /**
     * Get options object.
     *
     * @return Options
     */
    public function getOptions();
}
