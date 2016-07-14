<?php

namespace Burntromi\ExceptionGenerator\IntegrationTest;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Burntromi\ExceptionGenerator\Cli\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use org\bovigo\vfs\vfsStream;
use Burntromi\ExceptionGenerator\Generator\ExceptionClassNames;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Hook\Scope\AfterFeatureScope;

/**
 * Given/When/Thens/Hooks shared between features
 */
class FeatureContext extends AbstractContext implements Context, SnippetAcceptingContext
{
    /** @AfterFeature */
    public static function teardownFeature(AfterFeatureScope $scope)
    {

    }

    /**
     * @Given Directory structure with namespaces
     */
    public function directoryStructureWithNamespaces()
    {
        vfsStream::setup('root', null, array(
            'project' => array(
                'src' => array(
                    'Foo' => array(
                        'Bar' => array(
                            'My' => array()
                        )
                    )
                )
            )
        ));

        $this->getOptions()->set('path', vfsStream::url('root'));
        $this->getOptions()->set('home', $this->getOptions()->get('path'));
    }

    /**
     * @Given Application with current path :path
     */
    public function applicationWithCurrentPath($path)
    {
        $this->getOptions()->add('inputOptions', 'path', $this->getOptions()->get('path') . '/' . $path);
    }

    /**
     * @Given existing exception classes in path :path
     */
    public function existingExceptionClassesInPath($path)
    {
        $path = vfsStream::url('root/' . $path) . '/Exception/';

        mkdir($path, 0777, true);
        $namespace     = rtrim(str_replace(array(vfsStream::url('root/project/src/'), '/'), array('', '\\'), $path), '\\');
        $interface     = "<?php\nnamespace $namespace;\n\ninterface ExceptionInterface\n{\n}\n";
        $interfacePath = $path . 'ExceptionInterface.php';
        file_put_contents($interfacePath, $interface);

        $classNames = ExceptionClassNames::getExceptionClassNames();
        foreach ($classNames as $className) {
            $classContent = "<?php\nnamespace $namespace;\n\n"
                    . "use $className as Base$className;\n\n"
                    . "class $className extends Base$className implements ExceptionInterface\n{\n}\n";
            $classPath    = $path . $className . '.php';
            file_put_contents($classPath, $classContent);
        }
    }

    /**
     * @Given dummy files and folders in all directories
     */
    public function dummyFilesInAllDirectories()
    {
        $inputOptions  = $this->getOptions()->get('inputOptions');
        $path          = $inputOptions['path'];
        $characterList = array_merge(range(0, 9), range('a', 'z'));
        $rndString     = $this->randomString(7);

        do {
            foreach ($characterList as $char) {
                file_put_contents($path . '/' . $char . '_DummyFile_' . $rndString . '.txt', 'if you read this you can read');
                mkdir($path . '/' . $char . '_DummyDir_' . $rndString);
            }
            $path = dirname($path) !== 'vfs:' ? dirname($path) : 'vfs://';
        } while ($path !== 'vfs://');
    }

    /**
     * @Given File :filename is removed from :path
     */
    public function fileIsRemovedFrom($filename, $path)
    {
        $path = vfsStream::url('root/' . $path);
        assertTrue(unlink($path . $filename));
        assertFileNotExists($path . $filename);
    }

    /**
     * @Then File :filename is restored in :path
     */
    public function fileIsRestoredIn($filename, $path, PyStringNode $fileContents)
    {
        $file = vfsStream::url('root/' . $path) . $filename;
        assertFileExists($file);
        assertStringMatchesFormat($fileContents->getRaw(), file_get_contents($file));
    }

    /**
     * @When the application is executed
     */
    public function theApplicationIsExecuted()
    {
        $inputOptions = $this->getOptions()->get('inputOptions');
        $input        = new ArrayInput($inputOptions);
        $application  = new Application();
        $application->setHome($this->getOptions()->get('home'));
        $application->setAutoExit(false);
        $application->run($input);
    }

    private function randomString($length)
    {
        $key  = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }
}
