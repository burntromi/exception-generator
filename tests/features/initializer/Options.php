<?php

namespace Burntromi\ExceptionGenerator\IntegrationTest\Initializer;

class Options
{

    /**
     *
     * @var array
     */
    protected $options = array();

    /**
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get option.
     *
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    public function get($option, $default = null)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        return $default;
    }

    /**
     *
     * @param string $option
     * @param mixed $value
     */
    public function set($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Add a value to existing option
     *
     * @param string $option
     * @param string $key
     * @param mixed $value
     */
    public function add($option, $key, $value)
    {
        if (array_key_exists($option, $this->options)) {
            // @todo add behaviour when option is no array
            $this->options[$option][$key] = $value;
        }
    }

    /**
     * Delete option.
     *
     * @param string $option
     */
    public function delete($option)
    {
        unset($this->options[$option]);
    }
}
