<?php

/**
 * IPP - PHP Project Student
 * @author Shelest Oleksii (xshele02)
 * @file BlockClass.php
 * @brief Built-in Block class with its built-in methods
 * @date 22.04.2025
 */

namespace IPP\Student;

use DOMElement;
use IPP\Core\ReturnCode;

class BlockClass extends ObjectClass
{
    /** @var DOMElement */
    public mixed $value;

    /** @var array<string, mixed> */
    public array $closureAttributes;

    /** @var array<string, mixed> */
    public array $variables;

    public BuiltInClasses $classes;

    public int $arity;

    /** @var string[] */
    public array $paramNames = [];

    /**
     * @param array<string, mixed> $closureAttributes
     */
    public function __construct(
        DOMElement $blockNode,
        array &$closureAttributes,
        BuiltInClasses $classes
    ) {
        $this->value = $blockNode;
        $this->closureAttributes = &$closureAttributes;
        $this->variables = [];
        $this->classes = $classes;
        $this->arity = (int) $blockNode->getAttribute('arity');

        $params = [];
        foreach ($blockNode->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'parameter') {
                $order = (int) $child->getAttribute('order');
                $name = $child->getAttribute('name');
                $params[$order] = $name;
            }
        }

        ksort($params);
        $this->paramNames = array_values($params);
    }

    /**
     * @brief Executes the block with provided arguments.
     * @param ObjectClass[]|list<mixed> $args
     */
    public function value(array $args = []): mixed
    {
        if (count($args) !== $this->arity) {
            fwrite(STDERR, "Error: Block expected {$this->arity} arguments, got " . count($args) . "\n");
            exit(ReturnCode::INTERPRET_DNU_ERROR);
        }

        foreach ($this->paramNames as $index => $name) {
            $this->variables[$name] = $args[$index] ?? null;
        }

        $executor = new DOMExecutor($this->classes);
        $attributes = &$this->closureAttributes;
        $result = $executor->interpretBlockDOM($this->value, $this->variables, $attributes);

        return $result ?? new NilClass();
    }

    /**
     * @brief A function that checks the method name and
     *        calls the value function with parameters
     * @param ObjectClass[]|list<mixed> $args
     */
    public function call(string $name, array $args): mixed
    {
        if (preg_match('/^value(?::value)*:?$/', $name)) {
            return $this->value($args);
        }
        return null;
    }

    /**
     * @brief Built-in function whileTrue:
     * @param ObjectClass[]|list<mixed> $args
     */
    public function whileTrue(array $args): mixed
    {
        if (!isset($args[0]) || !$args[0] instanceof BlockClass) {
            fwrite(STDERR, "Error: Arg must be a block\n");
            exit(ReturnCode::INTERPRET_TYPE_ERROR);
        }

        $block = $args[0];
        while (true) {
            $result = $this->call('value', []);
            if ($result instanceof FalseClass) {
                break;
            }

            $block->call('value', []);
        }

        return new NilClass();
    }
}
