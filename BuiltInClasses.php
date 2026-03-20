<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file BuiltInClasses.php
 * @brief Сlass handles all classes in the code and stores information about them
 * @date 22.04.2025
 */

namespace IPP\Student;

use DOMDocument;
use DOMElement;
use IPP\Core\ReturnCode;

class BuiltInClasses
{
    /**
     * Registered classes by name
     *  @var array<string, UserDefinedClass> */
    private array $classes = [];

    /**
     * Currently selected class DOM element
     * @var DOMElement */
    public $currentClass;

    public function __construct(DOMDocument $dom)
    {
        $this->processClasses($dom);
    }

    private function processClasses(DOMDocument $dom): void
    {
        $classNodes = $dom->getElementsByTagName("class");

        foreach ($classNodes as $classNode) {
            $className = $classNode->getAttribute("name");
            $parentClass = $classNode->getAttribute("parent");

            if ($className === $parentClass) {
                fwrite(STDERR, "Error: class cannot inherit from itself\n");
                exit(ReturnCode::INTERPRET_TYPE_ERROR);
            }
            /** @var array<string, DOMElement> $methods */
            $methods = [];

            foreach ($classNode->getElementsByTagName("method") as $methodNode) {
                $selector = $methodNode->getAttribute("selector");
                if (!empty($selector)) {
                    $methods[$selector] = $methodNode;
                }
            }

            $this->registerClass($className, $parentClass, $methods);
        }
    }

    /**
     * Checks if a class is already registered
     */
    public function hasClass(string $className): bool
    {
        return isset($this->classes[$className]);
    }

    /**
     * Registers a new user-defined class if it is not already registered
     * @param array<string, DOMElement> $methods
     */
    public function registerClass(string $className, string $parent, array $methods): void
    {
        if (!$this->hasClass($className)) {
            $this->classes[$className] = new UserDefinedClass($className, $parent, $methods);
        }
    }


    /**
     * Finds and returns a DOMElement representing the specified class in the given program
     */
    public function findClassDOM(DOMElement $program, string $className): ?DOMElement
    {
        foreach ($program->getElementsByTagName('class') as $class) {
            if ($class->getAttribute('name') === $className) {
                $this->currentClass = $class; // Store current class
                return $class;
            }
        }
        return null;
    }


    /**
     * Finds and returns a <method> DOM node
     */
    public function findMethodDOM(DOMElement $class, string $selector): ?DOMElement
    {
        foreach ($class->getElementsByTagName('method') as $method) {
            if ($method->getAttribute('selector') === $selector) {
                return $method;
            }
        }
        return null;
    }

    public function getCurrentClass(): DOMElement
    {
        return $this->currentClass;
    }
}
