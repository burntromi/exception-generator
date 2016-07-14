<?php

namespace Burntromi\ExceptionGenerator\Generator;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use DateTime;

class TemplateRenderer
{
    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * Constructor.
     *
     * @param PhpRenderer $renderer Optional PhpRenderer instance
     */
    public function __construct(PhpRenderer $renderer = null)
    {
        $this->renderer = $renderer;
        if (null === $renderer) {
            $this->renderer = new PhpRenderer;
        }

        $this->renderer->setResolver(new TemplateMapResolver());
    }

    /**
     * Add a path to template resolver.
     *
     * @param string $type     Template type
     * @param string $template Template path
     */
    public function addPath($type, $template)
    {
        /* @var $resolver TemplateMapResolver */
        $resolver = $this->renderer->resolver();
        $resolver->add($type, $template);
    }

    /**
     * Render an exception template
     *
     * @param string $namespace     Namespace of class
     * @param string $use Path for BaseExceptions to use, if they exists
     * @param string $exceptionName Type of exception (if null a interface is rendered)
     * @return string
     */
    public function render($namespace, $use = null, $exceptionName = null)
    {
        $model = new ViewModel;
        //replace because it will be added in template anyway
        $namespace = str_replace('\\Exception', '', $namespace);
        if (null !== $exceptionName) {
            $model->setTemplate('exception');
            $model->setVariable('exceptionName', $exceptionName);
            $model->setVariable('use', empty($use) ? $exceptionName : $use);
        } else {
            $model->setTemplate('interface');
            $model->setVariable('use', $use);
        }
        $model->setVariable('namespace', $namespace);
        $model->setVariable('created', new DateTime());
        $model->setVariable('user', $this->getUsername());
        $content = $this->renderer->render($model);

        return $content;
    }

    private function getUsername()
    {
        if (defined('USER')) {
            return USER;
        }

        $user = getenv('USER');
        if (!empty($user)) {
            return $user;
        }

        $user = getenv('USERNAME');
        if (!empty($user)) {
            return $user;
        }

        return null;
    }

    /**
     *
     * @return PhpRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }
}
