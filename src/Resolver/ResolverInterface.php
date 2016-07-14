<?php

namespace Burntromi\ExceptionGenerator\Resolver;

interface ResolverInterface
{
    /**
     * Resolve namespace from file.
     *
     * @param string $path
     * @param array  $loopedDirectories
     * @return string|bool
     */
    public function resolve($path, array $loopedDirectories);
}
