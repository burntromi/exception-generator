<?php

namespace Burntromi\ExceptionGenerator\Resolver;

class ComposerResolver implements ResolverInterface
{
    /**
     * {@inheritDoc}
     */
    public function resolve($path, array $loopedDirectories)
    {
        $namespace = false;
        $jsonFile  = file_get_contents($path);
        $json      = json_decode($jsonFile, true);

        if (null !== $json && isset($json['autoload'])) {

            $autoload = $json['autoload'];
            if (isset($autoload['psr-4'])) {
                $namespaces = $autoload['psr-4'];
                $namespace = key($namespaces);
                $path = current($namespaces);
            } elseif (isset($autoload['psr-0'])) {
                $namespaces = $autoload['psr-0'];
                $namespace = key($namespaces);
                $path = current($namespaces);
            }

            if (false !== $namespace) {
                $namespace = rtrim(preg_replace('/\s+/', '', $namespace), '\\');

                $namespaceDiff = array_reverse(array_diff($loopedDirectories, explode('/', $path)));
                $namespaceDiff = array_diff($namespaceDiff, explode('\\', $namespace));

                if (count($namespaceDiff) > 0) {
                    $namespace .= '\\' . implode('\\', $namespaceDiff);
                }
                $namespace = ltrim($namespace, '\\');
            }
        }

        return $namespace;
    }
}
