<?php

namespace Burntromi\ExceptionGenerator\TemplateResolver;

use Burntromi\ExceptionGenerator\Exception\RuntimeException;

class TemplatePathMatcher
{
    const CONFIG_NAME = '.exception-generator.json';

    protected $currentDir;
    protected $configPath;

    /**
     * defines current dir and path for config
     *
     * @param string $currentDir
     * @param string $configPath
     */
    public function __construct($currentDir, $configPath)
    {
        $this->currentDir = $currentDir;
        $this->configPath = $configPath . '/' . self::CONFIG_NAME;
    }

    /**
     * checks if config is valid and returns matching paths
     *
     * @param string $templateName
     * @return boolean|string
     * @throws RuntimeException
     */
    public function match($templateName)
    {
        if (!is_readable($this->configPath)) {
            return false;
        }

        $jsonData = json_decode(file_get_contents($this->configPath), true);
        if (json_last_error() != JSON_ERROR_NONE || !is_array($jsonData)) {
            throw new RuntimeException("Could not parse json configuration \"$this->configPath\".");
        }

        return $this->getPaths($jsonData, $templateName);
    }

    /**
     * trys to get the most matching path or global from config
     *
     * @param array $configData
     * @param string $templateName
     * @return boolean|string
     */
    protected function getPaths(array $configData, $templateName)
    {
        if (!isset($configData['templatepath']) || !is_array($configData['templatepath'])) {
            return false;
        }

        $templatePath = $configData['templatepath'];

        if (isset($templatePath['projects']) && is_array($templatePath['projects'])) {
            $filteredProjects = $this->filterMatchingPaths($templatePath['projects']);

            if (false !== ($matchingPath = $this->getMostRelatedPath($filteredProjects, $templateName))) {
                return $matchingPath;
            }
        }

        if (isset($templatePath['global'])) {
            $globalPath = $templatePath['global'];
            if (file_exists($globalPath . '/' . $templateName)) {
                return $globalPath;
            }
        }

        return false;
    }

    /**
     * filters paths matching to current directory
     *
     * @param array $projects
     * @return array
     */
    public function filterMatchingPaths(array $projects)
    {
        $filteredProjects = array();
        foreach ($projects as $path => $projectTemplatePath) {
            // @todo Windows: case-insensitive match with stripos?
            if (false !== strpos($this->currentDir, $path)) {
                $filteredProjects[$path] = $projectTemplatePath;
            }
        }

        return $filteredProjects;
    }

    /**
     * trys to get the most related path where template was found
     *
     * @param array $filteredProjects
     * @param string $templateName
     * @return boolean|string
     */
    protected function getMostRelatedPath(array $filteredProjects, $templateName)
    {
        uksort($filteredProjects, function ($a, $b) {
            $strlenA = strlen($a);
            $strlenB = strlen($b);

            if ($strlenA < $strlenB) {
                return 1;
            }
            return -1;
        });

        $filteredProjects = array_values($filteredProjects);

        foreach ($filteredProjects as $templatePath) {
            if (file_exists($templatePath . '/' . $templateName)) {
                return $templatePath;
            }
        }
        return false;
    }
}
