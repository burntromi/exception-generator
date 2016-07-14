<?php

namespace Burntromi\ExceptionGenerator\IntegrationTest;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;

/**
 * Defines application features from the specific context.
 */
class CreateExceptionContext extends AbstractContext implements Context, SnippetAcceptingContext
{
    /**
     * @Given a path containing php classes with namespaces
     */
    public function aPathContainingPhpClassesWithNamespaces()
    {
        file_put_contents(
            $this->getOptions()->get('path') . '/project/src/Foo/MyClass.php',
            '<?php namespace Foo; class Test {}'
        );
    }

    /**
     * @Given a path containing php classes with namespaces in same path
     */
    public function aPathContainingPhpClassesWithNamespacesInSamePath()
    {
        file_put_contents(
            $this->getOptions()->get('path') . '/project/src/Foo/Bar/My/MyClass.php',
            '<?php namespace Foo\Bar\My; class Test {}'
        );
    }

    /**
     * @Given a path containing a composer.json with a :namespaceType namespace
     */
    public function aPathContainingAComposerJsonWithANamespace($namespaceType)
    {
        $composerJson = array('autoload' => array());

        switch ($namespaceType) {
            case 'psr-4':
                $composerJson['autoload']['psr-4'] = array(
                    'Foo\Bar\\' => 'src/Foo/Bar/'
                );
                break;
            case 'psr-0':
                $composerJson['autoload']['psr-0'] = array(
                    'Foo\Bar\\' => 'src/'
                );
                break;
            default:
                throw new \InvalidArgumentException('Invalid namespace type given');
        }
        file_put_contents(
                $this->getOptions()->get('path') . '/project/composer.json', json_encode($composerJson)
        );
    }

    /**
     * @Then a file named :file should be created in :path with content
     */
    public function aFileNamedShouldBeCreatedWithContent($file, $path, PyStringNode $content)
    {
        $file = $this->getOptions()->get('path') . $path . $file;
        assertFileExists($file);
        assertStringMatchesFormat($content->getRaw(), file_get_contents($file));
    }

    /**
     * @Given option for disabling parent exception search is :option
     */
    public function applicationWithDisabledParentSearch($option)
    {
        if ('set' === $option) {
            $this->getOptions()->add('inputOptions', '--no-parents', true);
        } else {
            $this->getOptions()->add('inputOptions', '--no-parents', false);
        }
    }
}
