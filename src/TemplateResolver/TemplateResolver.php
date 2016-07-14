<?php

namespace Burntromi\ExceptionGenerator\TemplateResolver;

class TemplateResolver
{
    /**
     * Path to template.
     *
     * @var string
     */
    protected $templatePath;

    /**
     * TemplatePathMatcher instance.
     *
     * @var TemplatePathMatcher
     */
    protected $templatePathMatcher;

    /**
     * transforms received path to a valid realpath
     *
     * @param string $templatePath
     * @param TemplatePathMatcher $templatePathMatcher
     */
    public function __construct($templatePath, TemplatePathMatcher $templatePathMatcher)
    {
        $this->templatePath        = rtrim($templatePath, '/');
        $this->templatePathMatcher = $templatePathMatcher;
    }

    /**
     * resolves path for specific template
     *
     * @param string $templateName
     * @return string
     */
    public function resolve($templateName)
    {
        return $this->getTemplatePath($templateName) . '/' . $templateName;
    }

    /**
     * trys different paths to resolve a valid template path
     *
     * @param string $templateName
     * @return string
     */
    protected function getTemplatePath($templateName)
    {
        if (file_exists($this->templatePath . '/' . $templateName)) {
            return $this->templatePath;
        }

        $match = $this->templatePathMatcher->match($templateName);

        if (false === $match) {
            return realpath(__DIR__ . '/../../templates');
        }

        return $match;
    }
}
