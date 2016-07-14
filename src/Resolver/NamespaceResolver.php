<?php

namespace Burntromi\ExceptionGenerator\Resolver;

use Burntromi\ExceptionGenerator\Exception\RuntimeException;

class NamespaceResolver implements ResolverInterface
{
    const T_WHITESPACE   = T_WHITESPACE;
    const T_NAMESPACE    = T_NAMESPACE;
    const T_NS_SEPARATOR = T_NS_SEPARATOR;
    const T_STRING       = T_STRING;

    public function resolve($path, array $loopedDirectories)
    {
        if (!is_readable($path)) {
            throw new RuntimeException('PHP file "' . $path . '" isn\'t readable');
        }

        $namespace = false;
        $tokens    = token_get_all(file_get_contents($path));

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $type = $token[0];
            } elseif ($namespace && $token === ';') {
                $namespace = ltrim(trim($namespace), '\\');
                
                // adding looped folders to namespace
                if (count($loopedDirectories) > 0) {
                    $namespace .= '\\' . implode('\\', array_reverse($loopedDirectories));
                }

                break;
            }

            if (self::T_NAMESPACE === $type) {
                $namespace = '';
                continue;
            }

            $lookForToken = (false !== $namespace && $type !== self::T_WHITESPACE);
            $validToken = ($type === self::T_STRING || $type === self::T_NS_SEPARATOR);

            if ($lookForToken && $validToken) {
                $namespace .= $token[1];
            } elseif ($lookForToken && !$validToken) {
                $namespace = false;
                break;
            }
        }

        return $namespace;
    }
}
