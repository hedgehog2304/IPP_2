<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file UserDefinedClass.php
 * @brief Class for non-built-in classes with their methods
 * @date 22.04.2025
 */

namespace IPP\Student;

use DOMElement;

class UserDefinedClass
{
    public string $name;
    public string $parent;
    /** @var array<string, mixed> */
    public array $methods;

    /**
     * @param array<string, mixed> $methods
     */
    public function __construct(string $name, string $parent, array $methods)
    {
        $this->name = $name;
        $this->parent = $parent;
        $this->methods = $methods;
    }

    public function hasMethod(string $selector): bool
    {
        return isset($this->methods[$selector]);
    }

    public function getMethodDOM(string $selector): ?DOMElement
    {
        $method = $this->methods[$selector] ?? null;
        return $method instanceof DOMElement ? $method : null;
    }
}
