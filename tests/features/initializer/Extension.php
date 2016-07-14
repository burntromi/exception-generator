<?php

namespace Burntromi\ExceptionGenerator\IntegrationTest\Initializer;

use Behat\Testwork\ServiceContainer\Extension as BehatExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Symfony\Component\DependencyInjection\Definition;

class Extension implements BehatExtension
{

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->addDefaultsIfNotSet()
            ->children()
                ->variableNode('options')->end()
            ->end();
    }

    public function getConfigKey()
    {
        return 'application_initializer';
    }

    public function initialize(ExtensionManager $extensionManager)
    {

    }

    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition(__NAMESPACE__ . '\ApplicationInitializer', array(
            $config['options']
        ));

        $definition->addTag(ContextExtension::INITIALIZER_TAG);
        $container->setDefinition('application_initializer', $definition);
    }

    public function process(ContainerBuilder $container)
    {

    }
}
