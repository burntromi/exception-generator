<?php

namespace Burntromi\ExceptionGenerator\Generator;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;
use \Zend\View\Renderer\PhpRenderer;

/**
 * @coversDefaultClass Burntromi\ExceptionGenerator\Generator\TemplateRenderer
 */
final class TemplateRendererTest extends TestCase
{

    /**
     * @var TemplateRenderer
     */
    private $object;
    private $resolver;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $templatePath   = vfsStream::url('test/templates');
        $this->resolver = $this->createMock('\Zend\View\Resolver\ResolverInterface');
        $this->resolver->expects($this->any())
                ->method('resolve')
                ->willReturnCallback(function ($name) use($templatePath) {
                    return $templatePath . '/' . $name . '.phtml';
                });

        $renderer     = new \Zend\View\Renderer\PhpRenderer;
        $renderer->setResolver($this->resolver);
        vfsStream::setup('test', null, array('templates' => array()));
        $this->object = new TemplateRenderer();
        $this->object->addPath('exception', vfsStream::url('test/templates/exception.phtml'));
        $this->object->addPath('interface', vfsStream::url('test/templates/interface.phtml'));
    }

    /**
     * @covers ::render
     * @covers ::__construct
     * @uses Burntromi\ExceptionGenerator\Generator\TemplateRenderer::addPath
     * @dataProvider renderTestTemplate
     */
    public function testRender($template, $templateName, $namespace, $exceptionName, $renderedFile)
    {
        $path = vfsStream::url('test/templates/' . $templateName . '.phtml');
        file_put_contents($path, $template);
        $this->assertSame($renderedFile, $this->object->render($namespace, null, $exceptionName));
    }

    /**
     * @return array
     */
    public function renderTestTemplate()
    {
        return array(
            array(
                'template'      => '<?php echo $namespace ?>',
                'templateName'  => 'interface',
                'namespace'     => 'foo\bar',
                'exceptionName' => null,
                'renderedFile'  => 'foo\bar',
            ),
            array(
                'template'      => '<?php echo $namespace ?> - <?php echo $exceptionName ?>',
                'templateName'  => 'exception',
                'namespace'     => 'foo\bar',
                'exceptionName' => 'Test',
                'renderedFile'  => 'foo\bar - Test',
            ),
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRenderer
     * @covers ::addPath
     */
    public function testZFRendererGetTemplatePathPassed()
    {
        $templateException = vfsStream::url('test/templates/exception.phtml');

        $renderer = new PhpRenderer;
        $object   = new TemplateRenderer($renderer);
        $object->addPath('exception', $templateException);

        $this->assertInstanceOf('\Zend\View\Renderer\PhpRenderer', $object->getRenderer());
        $this->assertSame($templateException, $object->getRenderer()->resolver()->resolve('exception'));
    }
}
