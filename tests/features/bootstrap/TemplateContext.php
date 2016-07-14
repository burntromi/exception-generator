<?php

namespace Burntromi\ExceptionGenerator\IntegrationTest;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use org\bovigo\vfs\vfsStream;

/**
 * Defines application features from the specific context.
 */
class TemplateContext extends AbstractContext implements Context, SnippetAcceptingContext
{
    /**
     * @Given Directory structure for templates
     */
    public function directoryStructureForTemplates()
    {
        vfsStream::setup('root', null, array(
            'project' => array(
                'templates' => array(
                    'exception.phtml' => 'ExceptionTemplateCurrentDir',
                    'interface.phtml' => 'InterfaceTemplateCurrentDir',
                ),
            ),
            'global_templates' => array(
                'exception.phtml' => 'ExceptionTemplateGlobal',
                'interface.phtml' => 'InterfaceTemplateGlobal',
            ),
            'project_templates' => array(
                'exception.phtml' => 'ExceptionTemplateProject',
                'interface.phtml' => 'InterfaceTemplateProject',
            )
        ));

        $this->getOptions()->set('path', vfsStream::url('root'));
        $this->getOptions()->set('home', $this->getOptions()->get('path'));
    }

    /**
     * @Given Template path is passed as option
     */
    public function templatePathIsPassedAsOption()
    {
        $this->getOptions()->add(
            'inputOptions',
            '--template-path',
            $this->getOptions()->get('path') . '/project/templates'
        );
    }

    /**
     * @Given Template path is not passed as option
     */
    public function templatePathIsNotPassedAsOption()
    {
        $options = $this->getOptions();
        $inputOptions = $options->get('inputOptions');
        unset($inputOptions['--template-path']);
        $options->set('inputOptions', $inputOptions);
    }

    /**
     * @Given interface template is remove from passed template path
     */
    public function interfaceTemplateIsRemoveFromPassedTemplatePath()
    {
        unlink($this->getOptions()->get('path') . '/project/templates/interface.phtml');
    }

    /**
     * @Given Project template path configured in config
     */
    public function projectTemplatePathConfiguredInConfig()
    {
        $path = $this->getOptions()->get('path');
        file_put_contents(
            $path . '/.exception-generator.json',
            '{"templatepath":{"projects":{"' . $path . '/project":"'
                . $path . '/project_templates"}}}'
        );
    }

    /**
     * @Given Global template path configured in config
     */
    public function globalTemplatePathConfiguredInConfig()
    {
        $path = $this->getOptions()->get('path');
        file_put_contents(
            $path . '/.exception-generator.json',
            '{"templatepath":{"global": "'. $path . '/global_templates"}}'
        );
    }

    /**
     * @Then templates from template path should have been used
     */
    public function templatesFromTemplatePathShouldHaveBeenUsed()
    {
        $path = $this->getOptions()->get('path');
        $exceptionFile = $path . '/project/Exception/BadMethodCallException.php';
        assertFileExists($exceptionFile);
        assertSame('ExceptionTemplateCurrentDir', file_get_contents($exceptionFile));
    }

    /**
     * @Then template from project configuration from global configuration should have been used
     */
    public function templateFromProjectConfigurationFromGlobalConfigurationShouldHaveBeenUsed()
    {
        $path = $this->getOptions()->get('path');
        $exceptionFile = $path . '/project/Exception/BadMethodCallException.php';
        assertFileExists($exceptionFile);
        assertSame('ExceptionTemplateProject', file_get_contents($exceptionFile));
    }

    /**
     * @Then template from global configuration from global configuration should have been used
     */
    public function templateFromGlobalConfigurationFromGlobalConfigurationShouldHaveBeenUsed()
    {
        $path = $this->getOptions()->get('path');
        $exceptionFile = $path . '/project/Exception/BadMethodCallException.php';
        assertFileExists($exceptionFile);
        assertSame('ExceptionTemplateGlobal', file_get_contents($exceptionFile));
    }

    /**
     * @Then template from passed path for interface shouldn't have been used
     */
    public function templateFromPassedPathForInterfaceShouldnTHaveBeenUsed()
    {
        $path = $this->getOptions()->get('path');
        $exceptionFile = $path . '/project/Exception/ExceptionInterface.php';
        assertFileExists($exceptionFile);
        assertNotSame('InterfaceTemplateCurrentDir', file_get_contents($exceptionFile));
    }
}
